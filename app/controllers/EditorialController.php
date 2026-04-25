<?php
namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\CSRF;
use App\Lib\Database;
use App\Lib\Notification;
use App\Lib\Session;

/**
 * Service éditorial — catalogue + commandes côté auteur + page publique
 */
class EditorialController extends BaseController
{
    private const ALLOWED_UPLOAD_TYPES = [
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/zip' => 'zip',
        'application/x-zip-compressed' => 'zip',
    ];
    private const MAX_UPLOAD_BYTES = 50 * 1024 * 1024; // 50 Mo

    private function db(): Database
    {
        return Database::getInstance();
    }

    private function findService(string $slug): ?object
    {
        $row = $this->db()->fetch("SELECT * FROM editorial_services WHERE slug = ? AND actif = 1", [$slug]);
        return $row ?: null;
    }

    private function findOrder(int $id, ?int $userId = null): ?object
    {
        $sql = "SELECT o.*, s.nom AS service_nom, s.slug AS service_slug, s.icon AS service_icon, s.sur_devis
                FROM editorial_orders o JOIN editorial_services s ON s.id = o.service_id
                WHERE o.id = ?";
        $params = [$id];
        if ($userId !== null) {
            $sql .= " AND o.user_id = ?";
            $params[] = $userId;
        }
        $row = $this->db()->fetch($sql, $params);
        return $row ?: null;
    }

    // =====================================================================
    // PUBLIC — page d'accroche pour visiteurs
    // =====================================================================
    public function publicServices(): void
    {
        $services = $this->db()->fetchAll("SELECT * FROM editorial_services WHERE actif = 1 ORDER BY ordre");
        $this->view('pages/services_editoriaux', [
            'titre'    => 'Services éditoriaux',
            'services' => $services,
        ]);
    }

    // =====================================================================
    // AUTEUR — catalogue + commande
    // =====================================================================
    public function servicesList(): void
    {
        Auth::requireAuthor();
        $services = $this->db()->fetchAll("SELECT * FROM editorial_services WHERE actif = 1 ORDER BY ordre");
        $this->authorView('editorial/services_list', [
            'titre'    => 'Services éditoriaux',
            'services' => $services,
        ]);
    }

    public function serviceDetail(string $slug): void
    {
        Auth::requireAuthor();
        $service = $this->findService($slug);
        if (!$service) { redirect('/auteur/services-editoriaux'); return; }

        $this->authorView('editorial/service_detail', [
            'titre'   => $service->nom,
            'service' => $service,
        ]);
    }

    public function orderForm(string $slug): void
    {
        Auth::requireAuthor();
        $service = $this->findService($slug);
        if (!$service) { redirect('/auteur/services-editoriaux'); return; }

        $this->authorView('editorial/order_form', [
            'titre'   => 'Commander : ' . $service->nom,
            'service' => $service,
        ]);
    }

    public function createOrder(string $slug): void
    {
        Auth::requireAuthor();
        CSRF::check();
        $service = $this->findService($slug);
        if (!$service) { redirect('/auteur/services-editoriaux'); return; }

        $user = Auth::user();
        $titre = trim($_POST['titre_projet'] ?? '');
        $description = trim($_POST['description_projet'] ?? '');
        $nbPages = (int) ($_POST['nombre_pages'] ?? 0) ?: null;

        if ($titre === '' || mb_strlen($description) < 20) {
            Session::flash('error', 'Titre obligatoire et description d\'au moins 20 caractères.');
            redirect('/auteur/services-editoriaux/' . $slug . '/commander');
            return;
        }

        $fichierUrl = $this->handleUpload($_FILES['fichier'] ?? null, $user->id);

        // Statut initial : prix fixe → 'accepte' (paiement direct), sinon attente devis
        $statut = $service->sur_devis ? 'en_attente_devis' : 'accepte';
        $montant = $service->sur_devis ? null : (float) $service->prix_usd;

        $orderId = $this->db()->insert('editorial_orders', [
            'user_id'            => $user->id,
            'service_id'         => $service->id,
            'titre_projet'       => mb_substr($titre, 0, 300),
            'description_projet' => $description,
            'fichier_url'        => $fichierUrl,
            'nombre_pages'       => $nbPages,
            'montant_propose'    => $montant,
            'devise'             => 'USD',
            'statut'             => $statut,
        ]);

        // Notifier les admins
        Notification::createForAdmins(
            'editorial_new_order',
            'Nouvelle commande éditoriale',
            $user->prenom . ' a commandé « ' . $service->nom . ' » : ' . mb_substr($titre, 0, 100),
            '/admin/services-editoriaux/' . $orderId,
            'mail'
        );

        Session::flash('success', $service->sur_devis
            ? 'Commande envoyée. On t\'envoie un devis sous 48h.'
            : 'Commande créée. Tu peux maintenant la payer.'
        );
        redirect('/auteur/mes-commandes-editoriales/' . $orderId);
    }

    public function myOrders(): void
    {
        Auth::requireAuthor();
        $orders = $this->db()->fetchAll(
            "SELECT o.*, s.nom AS service_nom, s.icon AS service_icon, s.slug AS service_slug
             FROM editorial_orders o JOIN editorial_services s ON s.id = o.service_id
             WHERE o.user_id = ?
             ORDER BY o.created_at DESC",
            [Auth::id()]
        );
        $this->authorView('editorial/my_orders', [
            'titre'  => 'Mes commandes éditoriales',
            'orders' => $orders,
        ]);
    }

    public function orderDetail(string $id): void
    {
        Auth::requireAuthor();
        $order = $this->findOrder((int) $id, Auth::id());
        if (!$order) { redirect('/auteur/mes-commandes-editoriales'); return; }

        $this->authorView('editorial/order_detail', [
            'titre' => 'Commande #' . $order->id,
            'order' => $order,
        ]);
    }

    public function payOrder(string $id): void
    {
        Auth::requireAuthor();
        $order = $this->findOrder((int) $id, Auth::id());
        if (!$order) { redirect('/auteur/mes-commandes-editoriales'); return; }

        // Seuls les commandes 'accepte' (devis approuvé OU prix fixe) sont payables
        if (!in_array($order->statut, ['accepte'], true) || $order->montant_propose === null) {
            Session::flash('error', 'Cette commande n\'est pas prête pour le paiement.');
            redirect('/auteur/mes-commandes-editoriales/' . $order->id);
            return;
        }

        $this->authorView('editorial/payment_choose', [
            'titre' => 'Paiement — ' . $order->service_nom,
            'order' => $order,
        ]);
    }

    // =====================================================================
    // SERVE FILE — accès sécurisé aux fichiers (auteur propriétaire ou admin)
    // =====================================================================
    public function serveFile(string $type, string $filename): void
    {
        Auth::requireLogin();
        $user = Auth::user();

        if (!in_array($type, ['uploads', 'deliveries'], true)) {
            http_response_code(404); exit('Not found');
        }

        // Anti-traversée
        $filename = basename($filename);

        $relPath = 'storage/editorial/' . $type . '/' . $filename;
        $absPath = BASE_PATH . '/' . $relPath;

        if (!file_exists($absPath)) {
            http_response_code(404); exit('Fichier introuvable');
        }

        // Vérifier que le user a le droit (admin OU propriétaire de la commande référençant ce fichier)
        $col = $type === 'uploads' ? 'fichier_url' : 'fichier_livraison_url';
        $order = $this->db()->fetch("SELECT user_id FROM editorial_orders WHERE {$col} = ?", [$relPath]);

        $isAdmin = ($user->role ?? '') === 'admin';
        $isOwner = $order && (int) $order->user_id === (int) $user->id;
        if (!$isAdmin && !$isOwner) {
            http_response_code(403); exit('Accès refusé');
        }

        $mime = mime_content_type($absPath) ?: 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($absPath));
        header('X-Content-Type-Options: nosniff');
        readfile($absPath);
        exit;
    }

    // =====================================================================
    // PRIVATE
    // =====================================================================
    private function handleUpload(?array $file, int $userId): ?string
    {
        if (!$file || empty($file['tmp_name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }
        if ((int) ($file['size'] ?? 0) > self::MAX_UPLOAD_BYTES) {
            Session::flash('error', 'Fichier trop volumineux (max 50 Mo).');
            return null;
        }
        $mime = $file['type'] ?? '';
        if (!isset(self::ALLOWED_UPLOAD_TYPES[$mime])) {
            Session::flash('error', 'Format non supporté (PDF, DOCX, ZIP uniquement).');
            return null;
        }
        $ext = self::ALLOWED_UPLOAD_TYPES[$mime];
        $name = 'order-' . $userId . '-' . time() . '-' . bin2hex(random_bytes(6)) . '.' . $ext;
        $rel = 'storage/editorial/uploads/' . $name;
        $abs = BASE_PATH . '/' . $rel;
        if (!is_dir(dirname($abs))) mkdir(dirname($abs), 0755, true);
        if (!move_uploaded_file($file['tmp_name'], $abs)) {
            Session::flash('error', 'Échec de l\'upload du fichier.');
            return null;
        }
        return $rel;
    }

    private function authorView(string $viewName, array $data = []): void
    {
        $this->view($viewName, $data, 'author');
    }
}
