<?php
class User{
    private $id;
    private $username;
    private $email;
    private $rol;
    private $pass;

    // Getter para el ID
    public function getId() {
        return $this->id;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getRol() {
        return $this->rol;
    }

    public function getPass() {
        return $this->pass;
    }

    // Setter para el ID
    public function setId($id) {
        $this->id = $id;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setRol($rol) {
        $this->rol = $rol;
    }

    public function setPass($pass) {
        $this->pass = $pass;
    }

    /**
     * El constructor ahora espera que el ID sea proporcionado por el usuario
     */
    public function __construct($id, $username = null, $email = null, $rol = null, $pass = null) {
        // El ID es obligatorio y representará el número de identificación proporcionado por el usuario
        $this->setId($id);
        $this->setUsername($username);
        $this->setEmail($email);
        $this->setRol($rol);
        $this->setPass($pass);
    }

    /**
     * Crea un usuario usando el ID provisto por el usuario
     */
    public function CreateUser($conexion, $estado = 'activo') {
        $sql = "INSERT INTO usuarios (id, user, email, pass, rol, estado) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        [$id, $username, $email, $pass, $rol] = [
            $this->getId(),
            $this->getUsername(),
            $this->getEmail(),
            $this->getPass(),
            $this->getRol()
        ];
        $stmt->bind_param("isssss", $id, $username, $email, $pass, $rol, $estado);

        if (!$stmt->execute()) {
            throw new Exception('Error al registrar usuario');
        }
        $stmt->close();

        return $this->getId();
    }

    /**
     * Actualiza un usuario existente, usando el ID interno
     */
    public function UpdateUser($conexion) {
        if (is_null($this->getId())) {
            throw new Exception('ID de usuario no definido para actualización');
        }

        $updatePassword = !empty($this->getPass());
        $password = $updatePassword ? password_hash($this->getPass(), PASSWORD_DEFAULT) : null;

        [$username, $email, $rol, $iduser] = [
            $this->getUsername(),
            $this->getEmail(),
            $this->getRol(),
            $this->getId()
        ];

        if ($updatePassword) {
            $sql = "UPDATE usuarios SET
                        user = ?,
                        email = ?,
                        pass = ?,
                        rol = ?
                    WHERE id = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("ssssi", $username, $email, $password, $rol, $iduser);
        } else {
            $sql = "UPDATE usuarios SET
                        user = ?,
                        email = ?,
                        rol = ?
                    WHERE id = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("sssi", $username, $email, $rol, $iduser);
        }

        if (!$stmt->execute()) {
            throw new Exception('Error al actualizar usuario');
        }
        $stmt->close();
    }

    public static function getUser($id, $conexion, $find_pending_user = false) {
        $sql = "SELECT * FROM usuarios WHERE id = ? AND estado != 'rechazado'";
        if (!$find_pending_user) {
            $sql .= " AND estado != 'pendiente'";
        }
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            throw new Exception('Usuario no encontrado');
        }
        $usuario = $result->fetch_assoc();
        $stmt->close();

        return $usuario;
    }

    public static function getUserByCedula($cedula, $conexion) {
        // Preparamos la consulta sólo contra la columna id
        $query = $conexion->prepare(
            "SELECT * FROM usuarios 
             WHERE id = ? 
               AND estado NOT IN ('pendiente', 'rechazado')"
        );
        $query->bind_param("s", $cedula);
        $query->execute();
        $results = $query->get_result();

        if ($results->num_rows !== 1) {
            throw new Exception('Esta cédula no está registrada o el usuario no está activo');
        }
        return $results->fetch_assoc();
    }

    public static function getUserByEmail($email, $conexion){
        $query = $conexion->prepare("SELECT * FROM usuarios WHERE email=? AND estado NOT IN ('pendiente', 'rechazado')");
        $query->bind_param("s", $email);
        $query->execute();
        if(!$query) throw new Exception('MYSQLI error: '. $conexion->error);
        $results = $query->get_result();

        if($results->num_rows != 1){
            throw new Exception("Este correo o usuario no existe");
        }
    
        $user = $results->fetch_assoc();
        return $user;
    }

    public static function ChangePassword($userid, $new_password, $conexion) {
        $changepass_query = $conexion->prepare(
            'UPDATE usuarios SET pass = ? WHERE id = ?'
        );
        $changepass_query->bind_param('si', $new_password, $userid);
        $changepass_query->execute();

        if ($changepass_query->errno) {
            throw new Exception($conexion->error);
        }
        $changepass_query->close();
    }

    public static function DeleteUser($iduser, $conexion) {
        $sql = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $iduser);
        if (!$stmt->execute()) {
            throw new Exception('Error al eliminar usuario');
        }
        $stmt->close();
    }

    public static function UpdateUserState($id, $state, $conexion) {
        $sql = 'UPDATE usuarios SET estado = ? WHERE id = ?';
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('si', $state, $id);
        if (!$stmt->execute()) {
            throw new Exception('Error al actualizar estado del usuario: ' . $conexion->error);
        }
        $stmt->close();
    }
}
