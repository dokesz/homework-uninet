<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];
if ($method == "OPTIONS") {
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
    header("HTTP/1.1 200 OK");
    die();
}

use Phalcon\Encryption\Security;

class UserController extends BaseController
{
    function listAction()
    {

        if (!$this->routeGuard("user.read")) {
            return;
        }

        $response = (object)[];

        // Modify the query to join with the permissions table
        $users = $this->db->query("
            SELECT u.*, up.permission_id AS permission
            FROM users u
            LEFT JOIN user_permissions up ON u.id = up.user_id
            WHERE u.deleted = false
        ")->fetchAll(PDO::FETCH_OBJ);

        $response->people = [];
        foreach ($users as $user) {
            $userId = $user->id;
            if (!isset($response->people[$userId])) {
                $response->people[$userId] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'permissions' => []
                ];
            }
            if ($user->permission) {
                $response->people[$userId]['permissions'][] = $user->permission;
            }
        }

        // Re-index the array to remove gaps in the keys
        $response->people = array_values($response->people);

        return $this->response->setJsonContent($response);
    }

    function createAction()
    {
        if (!$this->routeGuard("user.write")) {
            return;
        }

        $request = $this->request->getJsonRawBody();

        $security = new Security();
        $hashedPassword = $security->hash($request->password, [
            'cost' => 10,
            'algo' => PASSWORD_ARGON2I
        ]);

        $user = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => $hashedPassword,
            'deleted' => false
        ];

        $this->db->execute(
            "INSERT INTO users (name, email, password, deleted) VALUES (:name, :email, :password, :deleted)",
            [
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => $user['password'],
                'deleted' => $user['deleted'] ? 'true' : 'false'
            ]
        );

        return $this->response->setJsonContent($user);
    }

    function readAction($id)
    {
        if (!$this->routeGuard("user.read")) {
            return;
        }

        $user = $this->db->query("SELECT * FROM users WHERE id = $id AND deleted = false")->fetch();

        return $this->response->setJsonContent($user);
    }

    function updateAction($id)
    {
        if (!$this->routeGuard("user.write")) {
            return;
        }

        $request = $this->request->getJsonRawBody();

        error_log(json_encode($request));

        $user = $this->db->query("SELECT * FROM users WHERE id = $id AND deleted = false")->fetch(PDO::FETCH_OBJ);

        if (!$user) {
            throw new \Exception("User not found");
        }

        $user->name = $request->name ?? $user->name;
        $user->email = $request->email ?? $user->email;

        $params = [
            'name' => $user->name,
            'email' => $user->email,
            'id' => $user->id
        ];

        $updateQuery = "UPDATE users SET name = :name, email = :email";

        if (isset($request->password) && !empty($request->password)) {
            $security = new Security();
            $hashedPassword = $security->hash($request->password, [
                'algo' => Security::CRYPT_ARGON2I
            ]);
            $user->password = $hashedPassword;
            $updateQuery .= ", password = :password";
            $params['password'] = $user->password;
        }

        $updateQuery .= " WHERE id = :id";

        $this->db->execute($updateQuery, $params);

        $this->db->execute("DELETE FROM user_permissions WHERE user_id = :user_id", ['user_id' => $user->id]);

        if (!empty($request->permissions)) {
            foreach ($request->permissions as $permission) {
                $this->db->execute(
                    "INSERT INTO user_permissions (user_id, permission_id) VALUES (:user_id, :permission_id)",
                    [
                        'user_id' => $user->id,
                        'permission_id' => $permission
                    ]
                );
            }
        }

        return $this->response->setJsonContent($user);
    }

    function deleteAction($id)
    {
        if (!$this->routeGuard("user.write")) {
            return;
        }

        $user = $this->db->query("SELECT * FROM users WHERE id = $id AND deleted = false")->fetch(PDO::FETCH_OBJ);

        if (!$user) {
            throw new \Exception("User not found");
        }

        $this->db->execute("UPDATE users SET deleted = true WHERE id = :id", ['id' => $id]);

        return $this->response->setJsonContent(['success' => true]);
    }
}
