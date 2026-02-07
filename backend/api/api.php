<?php

function api_cache_file(): string
{
    return __DIR__ . "/../data.json";
}

function api_fetch_remote_and_cache(): array
{
    try {
        $url = "https://randomuser.me/api/?results=20";
        $response = @file_get_contents($url);
        if ($response === false) {
            throw new Exception("No se pudo obtener datos de la API externa");
        }

        $data = json_decode($response, true);
        if (!isset($data['results']) || !is_array($data['results'])) {
            throw new Exception("Respuesta de la API externa inválida");
        }

        $users = [];
        foreach ($data['results'] as $user) {
            $users[] = [
                'id' => $user['login']['uuid'] ?? null,
                'nombre' => trim(($user['name']['first'] ?? '') . ' ' . ($user['name']['last'] ?? '')),
                'email' => $user['email'] ?? null,
                'telefono' => $user['phone'] ?? null,
                'pais' => $user['location']['country'] ?? null,
                'foto' => $user['picture']['thumbnail'] ?? null,
            ];
        }

        file_put_contents(api_cache_file(), json_encode($users));
        return $users;

    } catch (Exception $e) {
        error_log("Error en api_fetch_remote_and_cache: " . $e->getMessage());
        return [];
    }
}

function api_get_all_users(): array
{
    try {
        $cache = api_cache_file();
        if (!file_exists($cache)) {
            return api_fetch_remote_and_cache();
        }

        $content = @file_get_contents($cache);
        if ($content === false) throw new Exception("No se pudo leer cache");

        $decoded = json_decode($content, true);
        if (!is_array($decoded)) throw new Exception("Cache corrupto");

        return $decoded;

    } catch (Exception $e) {
        error_log("Error en api_get_all_users: " . $e->getMessage());
        return [];
    }
}

function api_filter_users(array $users, ?string $nombre, ?string $pais, ?string $email): array
{
    return array_values(array_filter($users, function ($user) use ($nombre, $pais, $email) {
        $ok = true;
        if ($nombre !== null && $nombre !== '') $ok = $ok && stripos($user['nombre'] ?? '', $nombre) !== false;
        if ($pais !== null && $pais !== '') $ok = $ok && stripos($user['pais'] ?? '', $pais) !== false;
        if ($email !== null && $email !== '') $ok = $ok && stripos($user['email'] ?? '', $email) !== false;
        return $ok;
    }));
}

function api_delete_user(string $id): bool
{
    try {
        $cache = api_cache_file();
        if (!file_exists($cache)) return false;

        $users = json_decode(file_get_contents($cache), true);
        if (!is_array($users)) throw new Exception("Cache corrupto");

        $before = count($users);
        $users = array_filter($users, fn($u) => ($u['id'] ?? '') !== $id);
        $after = count($users);

        file_put_contents($cache, json_encode(array_values($users)));
        return $after < $before;

    } catch (Exception $e) {
        error_log("Error en api_delete_user: " . $e->getMessage());
        return false;
    }
}

/* -------- Enrutador -------- */
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    header('Content-Type: application/json');

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $isDelete = ($method === 'DELETE') || ($method === 'POST' && isset($_GET['id']));

    try {

        if ($method === 'GET') {
            $nombre = $_GET['nombre'] ?? null;
            $pais = $_GET['pais'] ?? null;
            $email = $_GET['email'] ?? null;

            $users = api_get_all_users();
            $filtered = api_filter_users($users, $nombre, $pais, $email);

            echo json_encode(['results' => $filtered]);
            exit;
        }

        if ($isDelete) {
            if (!isset($_GET['id'])) {
                http_response_code(400);
                throw new Exception("ID requerido para eliminar usuario");
            }

            $ok = api_delete_user($_GET['id']);
            if ($ok) {
                echo json_encode(['message' => 'Usuario eliminado']);
                exit;
            } else {
                http_response_code(404);
                throw new Exception("Usuario no encontrado");
            }
        }

        http_response_code(405);
        throw new Exception("Método no permitido");

    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}
