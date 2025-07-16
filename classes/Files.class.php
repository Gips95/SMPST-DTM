<?php
class ProjectFile{

    private $name;
    private $size;
    private $type;
    private $route; 

    public function getName() { return $this->name; }
    public function getSize() { return $this->size; }
    public function getType() { return $this->type; }
    public function getRoute() { return $this->route; }

    public function setName($name) { $this->name = $name; }
    public function setSize($size) { $this->size = $size; }
    public function setType($type) { $this->type = $type; }
    public function setRoute($route) { $this->route = $route; }

    public function __construct($name = null, $size = null, $type = null, $route = null){
        $this->setName($name);
        $this->setSize($size);
        $this->setType($type);
        $this->setRoute($route);
    }

    public function CreateProjectFile($proyecto_id, $rutaAbsoluta, $conexion) {
        $nombreArchivoOriginal = $this->getName();
        $rutaFinal = $this->getRoute();
        $fileSize = $this->getSize();
        $type = $this->getType();
        
        // 1. Crear variable para la ruta completa
        $rutaCompleta = $rutaFinal . $nombreArchivoOriginal;
    
        $sqlArchivo = "INSERT INTO archivos (proyecto_id, nombre, ruta, tipo, size) VALUES (?, ?, ?, ?, ?)";
        $stmtArchivo = $conexion->prepare($sqlArchivo);
        
        if (!$stmtArchivo) {
            throw new Exception("Error preparando consulta: " . $conexion->error);
        }
        
        // 2. Usar la variable en lugar de la expresión
        $stmtArchivo->bind_param("isssi", 
            $proyecto_id, 
            $nombreArchivoOriginal, 
            $rutaCompleta,  // Variable, no expresión
            $type, 
            $fileSize
        );
        
        // 3. Mover el archivo usando la misma variable
        if (!move_uploaded_file($rutaAbsoluta, $rutaCompleta)) {
            throw new Exception("Error al mover el archivo.");
        }
    
        if (!$stmtArchivo->execute()) {
            throw new Exception("Error insertando archivo: " . $stmtArchivo->error);
        }
        
        $archivo_id = $conexion->insert_id;
        $stmtArchivo->close();
        
        return $archivo_id;
    }

    public static function getProjectFiles($proyectid, $conexion){
     
        $sql = "SELECT * FROM archivos WHERE proyecto_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $proyectid);
        if(!$stmt->execute()) throw new Exception('Hubo un error al obtener los archivos');
        $result = $stmt->get_result();
    
        $archivos = [];
        while ($row = $result->fetch_assoc()) {
            $archivos[] = $row;
        }
        $stmt->close();
        return $archivos;
        
    }

    public static function getFile($idarchivo, $conexion){
        // Primero, obtener la ruta del archivo a eliminar
        $sqlSelect = "SELECT * FROM archivos WHERE id = ?";
        $stmtSelect = $conexion->prepare($sqlSelect);
        $stmtSelect->bind_param("i", $idarchivo);
        $stmtSelect->execute();
        $result = $stmtSelect->get_result();
        if ($result->num_rows === 0) {
            throw new Exception('Archivo no encontrado');
        }
        $archivo = $result->fetch_assoc();
        $stmtSelect->close();

        return $archivo;
    }

    public static function UpdateFileType($archivoid, $nuevotipo, $conexion){
        $stmt = $conexion->prepare("UPDATE archivos SET tipo_archivo = ? WHERE id = ?");
        $stmt->bind_param("si", $nuevotipo, $archivoid);
        if(!$stmt->execute()) throw new Exception('Error al actualizar el tipo de archivo');
        $stmt->close();

    }

    public static function DeleteProjectFile($idarchivo, $ruta, $conexion){
        // Eliminar el archivo físico
        if (file_exists($ruta)) {
            if (!unlink($ruta)) {
                throw new Exception('No se pudo eliminar el archivo físico');
            }
        }
        // Eliminar el registro de la base de datos
        $sqlDelete = "DELETE FROM archivos WHERE id = ?";
        $stmtDelete = $conexion->prepare($sqlDelete);
        $stmtDelete->bind_param("i", $idarchivo);
        if (!$stmtDelete->execute()) {
            throw new Exception('Error al eliminar el archivo: ' . $stmtDelete->error);
        }
        $stmtDelete->close();
    }

    public static function DeleteProjectFiles($route, $projectid, $conexion){
        // 3. Eliminar archivos físicos

            if (file_exists($route)) {
                if (!unlink($route)) {
                    throw new Exception("Error eliminando archivo: $route");
                }
            }
        // 4. Eliminar registros de la base de datos
        $sql_delete_archivos = "DELETE FROM archivos WHERE proyecto_id = ?";
        $stmt_delete_archivos = $conexion->prepare($sql_delete_archivos);
        $stmt_delete_archivos->bind_param("i", $projectid);
        $stmt_delete_archivos->execute();
    }

    public static function DeleteDir($dir){
        if (!file_exists($dir)) return true;
    
    $files = array_diff(scandir($dir), ['.','..']);
    foreach ($files as $file) {
        $path = "$dir/$file";
        is_dir($path) ? self::DeleteDir($path) : unlink($path);
    }
    return rmdir($dir);
    }
}
?>