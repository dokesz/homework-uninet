<?php

class BaseController extends \Phalcon\Mvc\Controller
{
    function routeGuard($permissionCode)
    {
        $authHeader = $this->request->getHeader('Authorization');

        if (!$authHeader || $authHeader == 'Bearer null') {
            error_log("No Authorization header found");
            $this->dispatcher->forward([
                'controller' => 'error',
                'action' => 'unauthorized'
            ]);
            return;
        }

        list($bearer, $token) = explode(' ', $authHeader);

        $session = $this->db->query(
            "SELECT * FROM user_sessions WHERE token = :token AND expires_at > NOW()",
            ['token' => $token]
        )->fetch(PDO::FETCH_ASSOC);

        if (strtotime($session['expires_at']) < time()) {
            error_log("Token is expired, invalid, or missing 'expires_at'");
            $this->dispatcher->forward([
                'controller' => 'error',
                'action' => 'unauthorized'
            ]);
            return;
        }

        $userId = $session['user_id'];

        $permission = $this->db->query(
            "SELECT id FROM permissions WHERE code = :code",
            ['code' => $permissionCode]
        )->fetch();

        if (!$permission) {
            error_log("Permission code not found: " . $permissionCode);
            $this->dispatcher->forward([
                'controller' => 'error',
                'action' => 'forbidden'
            ]);
        }

        $permissionId = $permission->id;

        $userPermission = $this->db->query(
            "SELECT * FROM user_permissions WHERE user_id = :user_id AND permission_id = :permission_id",
            ['user_id' => $userId, 'permission_id' => $permissionId]
        )->fetch();

        if (!$userPermission) {
            error_log("Permission not found for user_id: " . $userId . " and permission_id: " . $permissionId);
            $this->dispatcher->forward([
                'controller' => 'error',
                'action' => 'forbidden'
            ]);
        }

        return true;
    }
}
