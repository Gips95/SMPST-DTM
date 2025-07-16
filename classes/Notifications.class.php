<?php
class Notification{

    public static function CreateNotification($id_element, $type, $details, $conexion){
        $stmt = $conexion->prepare('INSERT INTO notifications (id_element, type, details) VALUES (?,?,?)');
        $stmt->bind_param('iss', $id_element, $type, $details);
        $stmt->execute();
        $stmt->close();

        $notification_id = $conexion->insert_id;
        return $notification_id;
    }

    public static function AssignToUser($notification_id, $user_id, $conexion){
        $stmt = $conexion->prepare('INSERT INTO recipient_notifications (notification_id, user_id) VALUES (?,?)');
        $stmt->bind_param('ii', $notification_id, $user_id);
        $stmt->execute();
        $stmt->close();

        $last_insert = $conexion->insert_id;
        return $last_insert;
    }

    public static function AssignToRol($notification_id, $role, $conexion){
        $stmt = $conexion->prepare('INSERT INTO role_notifications (notification_id, role) VALUES (?,?)');
        $stmt->bind_param('is', $notification_id, $role);
        $stmt->execute();
        $stmt->close();

        $last_insert = $conexion->insert_id;
        return $last_insert;
    }

    public static function getAllNotifications($userid, $rol, $conexion){
        $sql = 'SELECT n.* FROM notifications n';
        $params = [];
        $types = '';

        if(!empty($userid)){
            $sql.='LEFT JOIN recipient_notifications recn ON n.id = recn.notification_id AND recn.user_id = ?';
            $params[] = $user_id;
            $types.='i';
        }

        if(!empty($rol)){
            $sql.='LEFT JOIN role_notifications roln ON n.id = roln.notification_id AND roln.role = ?';
            $params[] = $rol;
            $types.='s';
        }

        $sql.='ORDER BY n.created_at DESC';
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();

        $notifications = $res->fetch_all(MYSQLI_ASSOC);
        return $notifications;
    }
}
?>