<?php

require_once ROOT . "app/core/Database.php";

class User
{
    private static $conn;

    /**
     * Inicializa la conexión a la base de datos de forma estática.
     * Esto permite que los métodos estáticos accedan a la conexión sin necesidad de instanciar la clase.
     */
    private static function __constructStatic()
    {
        if (self::$conn === null) {
            $database = Database::getInstance();
            self::$conn = $database->getConnection();
        }
    }

    /**
     * Obtiene todos los usuarios de la base de datos.
     * @return array Un array de objetos de usuario.
     */
    public static function getAll()
    {
        self::__constructStatic();
        try {
            $sql = "SELECT id_usuario, rfc, nombre, apellido_paterno, apellido_materno, email, rol, activo FROM usuarios ORDER BY nombre ASC";
            $stmt = self::$conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getAll de User: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Busca un usuario por su ID.
     * @param int $id El ID del usuario.
     * @return array|false Un array asociativo con los datos del usuario o false si no se encuentra.
     */
    public static function getById($id)
    {
        self::__constructStatic();
        try {
            $sql = "SELECT id_usuario, rfc, nombre, apellido_paterno, apellido_materno, email, rol, activo, url_avatar FROM usuarios WHERE id_usuario = ?";
            $stmt = self::$conn->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getById de User: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Valida las credenciales de un usuario.
     * @param string $email El correo electrónico del usuario.
     * @param string $password La contraseña en texto plano.
     * @return array|false Un array con los datos del usuario si las credenciales son válidas, o false si no lo son.
     */
    public static function login($email, $password)
    {
        self::__constructStatic();
        try {
            // 1. Buscar al usuario por su correo electrónico
            $sql = "SELECT * FROM usuarios WHERE email = ? AND activo = 1";
            $stmt = self::$conn->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // 2. Verificar la contraseña
            if ($user && password_verify($password, $user['password_hash'])) {
                // Las credenciales son válidas
                return $user;
            } else {
                // Credenciales inválidas
                return false;
            }
        } catch (PDOException $e) {
            error_log("Error en login de User: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea un nuevo usuario en la base de datos.
     * @param array $data Un array con los datos del usuario a crear.
     * @return int|false El ID del nuevo usuario o false en caso de error.
     */
    public static function create($data)
    {
        self::__constructStatic();
        try {
            $sql = "INSERT INTO usuarios (rfc, nombre, apellido_paterno, apellido_materno, email, password_hash, telefono, rol, activo)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = self::$conn->prepare($sql);
            $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt->execute([
                $data['rfc'],
                $data['nombre'],
                $data['apellido_paterno'],
                $data['apellido_materno'],
                $data['email'],
                $password_hash,
                $data['telefono'],
                $data['rol'],
                1 // Por defecto, el usuario es activo
            ]);
            return self::$conn->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error en create de User: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza los datos de un usuario.
     * @param int $id El ID del usuario a actualizar.
     * @param array $data Un array con los datos a modificar.
     * @return bool True si la operación fue exitosa, false en caso de error.
     */
    public static function update($id, $data)
    {
        self::__constructStatic();
        try {
            $fields = [];
            $values = [];
            foreach ($data as $key => $value) {
                $fields[] = "$key = ?";
                $values[] = $value;
            }
            $values[] = $id;
            
            $sql = "UPDATE usuarios SET " . implode(', ', $fields) . " WHERE id_usuario = ?";
            $stmt = self::$conn->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            error_log("Error en update de User: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un usuario (cambia su estado a inactivo).
     * @param int $id El ID del usuario a eliminar.
     * @return bool True si la operación fue exitosa, false en caso de error.
     */
    public static function delete($id)
    {
        self::__constructStatic();
        try {
            $sql = "UPDATE usuarios SET activo = 0 WHERE id_usuario = ?";
            $stmt = self::$conn->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error en delete de User: " . $e->getMessage());
            return false;
        }
    }
}