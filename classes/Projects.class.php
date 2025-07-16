<?php
class Project
{
    private $title;
    private $description;
    private $authors;
    private $tutor;  // Corregido de tuthors a tutor
    private $investigation_line;
    private $entity;
    private $fecha;  // Nuevo campo

    public function getTitle()
    {
        return $this->title;
    }
    public function getDescription()
    {
        return $this->description;
    }
    public function getAuthors()
    {
        return $this->authors;
    }
    public function getTutor()
    {  // Corregido de getTuthors
        return $this->tutor;
    }
    public function getInvestigation_line()
    {
        return $this->investigation_line;
    }
    public function getEntity()
    {
        return $this->entity;
    }
    public function getFecha()
    {  // Nuevo getter
        return $this->fecha;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }
    public function setDescription($description)
    {
        $this->description = $description;
    }
    public function setAuthors($authors)
    {
        $this->authors = $authors;
    }
    public function setTutor($tutor)
    {  // Corregido de setTuthors
        $this->tutor = $tutor;
    }
    public function setInvestigation_line($invest_line)
    {
        $this->investigation_line = $invest_line;
    }
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }
    public function setFecha($fecha)
    {  // Nuevo setter
        $this->fecha = $fecha;
    }

    public function __construct(
        $title = null,
        $description = null,
        $authors = null,
        $tutor = null,  // Corregido y añadido
        $invest_line = null,
        $entity = null,
        $fecha = null   // Nuevo parámetro
    ) {
        $this->setTitle($title);
        $this->setDescription($description);
        $this->setAuthors($authors);
        $this->setTutor($tutor);
        $this->setInvestigation_line($invest_line);
        $this->setEntity($entity);
        $this->setFecha($fecha);
    }

    public function CreateProject($conexion)
    {
        // Preparar carpeta para guardar archivos
        $nombreCarpeta = preg_replace('/[^A-Za-z0-9_-]/', '_', $this->getTitle());
        $uploadDir = "../uploads/" . $nombreCarpeta . "/";

        // Crear directorio principal
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Directorio para referencias
        $rutaReferencias = $uploadDir . "referencias/";
        if (!file_exists($rutaReferencias)) {
            mkdir($rutaReferencias, 0777, true);
        }

        // Consulta actualizada con nuevos campos
        $sqlProyecto = "INSERT INTO proyectos 
                        (titulo, descripcion, autores, linea_investigacion, ente, tutor, fecha) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conexion->prepare($sqlProyecto);
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $conexion->error);
        }

        $titulo = $this->getTitle();
        $descripcion = $this->getDescription();
        $autores = $this->getAuthors();
        $linea_investigacion = $this->getInvestigation_line();
        $ente = $this->getEntity();
        $tutor = $this->getTutor();
        $fecha = $this->getFecha();

        $stmt->bind_param(
            "sssssss",
            $titulo,
            $descripcion,
            $autores,
            $linea_investigacion,
            $ente,
            $tutor,
            $fecha
        );

        if (!$stmt->execute()) {
            throw new Exception("Error al insertar: " . $stmt->error);
        }
        $stmt->close();

        $proyecto_id = $conexion->insert_id;
        return [$proyecto_id, $uploadDir, $rutaReferencias];
    }
    public static function getProject($projectid, $conexion)
    {
        $sql = "SELECT * FROM proyectos WHERE id = ? AND activo = 1";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $projectid);
        if (!$stmt->execute()) throw new Exception('error al ejecutar consulta');
        $result = $stmt->get_result();
        if ($result->num_rows == 0) throw new Exception('Projecto no encontrado');
        $proyecto = $result->fetch_assoc();
        $stmt->close();

        return $proyecto;
    }
    public function UpdateProject($projectid, $conexion)
    {
        // Consulta actualizada
        $sql = "UPDATE proyectos 
                SET titulo=?, descripcion=?, autores=?, 
                    linea_investigacion=?, ente=?, tutor=?, fecha=? 
                WHERE id=?";

        [$title, $description, $authors, $invest_line, $entity, $tuthors, $fecha] = [$this->getTitle(), $this->getDescription(), $this->getAuthors(), $this->getInvestigation_line(), $this->getEntity(), $this->getTutor(), $this->getFecha()];

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param(
            "sssssssi",
            $title,
            $description,
            $authors,
            $invest_line,
            $entity,
            $tuthors,
            $fecha,
            $projectid
        );

        if (!$stmt->execute()) {
            throw new Exception('Error al actualizar: ' . $stmt->error);
        }
        $stmt->close();
    }
    public static function FilterProjects(
        $busqueda,
        $ente,
        $conexion,
        
        $tutor = null,
        $fecha = null,
        $año = null,
        $linea_investigacion = null, // Nuevo parámetro aquí
        $orden = 'ASC',
        $page = 1,
        $per_page = 5
    ) {
        try {
            // Construcción de la consulta base
            $query = "SELECT * FROM proyectos WHERE 1 AND activo = 1";
            $countQuery = "SELECT COUNT(*) FROM proyectos WHERE 1 AND activo = 1"; // Consulta para contar
    
            $params = array();
            $types = "";
    
            // Búsqueda general
            if (!empty($busqueda)) {
                $query .= " AND (titulo LIKE ? OR descripcion LIKE ? OR autores LIKE ? OR linea_investigacion LIKE ?)";
                $countQuery .= " AND (titulo LIKE ? OR descripcion LIKE ? OR autores LIKE ? OR linea_investigacion LIKE ?)";
                $searchTerm = '%' . $busqueda . '%';
                array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
                $types .= "ssss";
            }
    
            // Filtro por ente
            if (!empty($ente)) {
                $query .= " AND ente = ?";
                $countQuery .= " AND ente = ?";
                $params[] = $ente;
                $types .= "s";
            }
    
            // Filtro por tutor
            if (!empty($tutor)) {
                $query .= " AND tutor LIKE ?";
                $countQuery .= " AND tutor LIKE ?";
                $params[] = '%' . $tutor . '%';
                $types .= "s";
            }
    
            // Filtro por fecha
            if (!empty($fecha)) {
                $query .= " AND fecha = ?";
                $countQuery .= " AND fecha = ?";
                $params[] = $fecha;
                $types .= "s";
            }

            //filtrar por año
            if(!empty($año)){
                $query .= " AND YEAR(fecha) = ?";
                $countQuery .= " AND YEAR(fecha) = ?";
                $params[] = $año;
                $types .= "s";
            }
        
            if (!empty($linea_investigacion)) {
                $query .= " AND linea_investigacion = ?";
                $countQuery .= " AND linea_investigacion = ?";
                $params[] = $linea_investigacion;
                $types .= "s";
            }
    
            // Ordenación
            $orden_valido = in_array(strtoupper($orden), ['ASC', 'DESC']) ? strtoupper($orden) : 'ASC';
            $query .= " ORDER BY fecha $orden_valido, titulo ASC";
    
            // Calcular el offset
            $offset = ($page - 1) * $per_page;
    
            // Añadir LIMIT a la consulta principal
            $query .= " LIMIT ? OFFSET ?";
            $params[] = $per_page;
            $params[] = $offset;
            $types .= "ii"; // Dos enteros para LIMIT y OFFSET
    
            // Preparar y ejecutar la consulta de conteo
            $countStmt = $conexion->prepare($countQuery);
            if ($countStmt === false) {
                throw new Exception("Error en la preparación de la consulta de conteo: " . $conexion->error);
            }
    
            if (!empty($params)) {
                // Crear una copia de los parámetros para la consulta de conteo
                $countParams = $params;
                $countTypes = $types;
    
                // Eliminar los parámetros de LIMIT y OFFSET de la consulta de conteo
                array_pop($countParams);
                array_pop($countParams);
                $countTypes = substr($countTypes, 0, -2); // Eliminar los últimos dos caracteres ('ii')
    
                if (!empty($countParams)) {
                    $countStmt->bind_param($countTypes, ...$countParams);
                }
            }
    
            if (!$countStmt->execute()) {
                throw new Exception('Error al ejecutar la consulta de conteo');
            }
    
            $countResult = $countStmt->get_result();
            $total_records = $countResult->fetch_row()[0];
            $total_pages = ceil($total_records / $per_page);
    
            $countStmt->close();
    
            // Preparar y ejecutar consulta principal
            $stmt = $conexion->prepare($query);
            if ($stmt === false) {
                throw new Exception("Error en la preparación: " . $conexion->error);
            }
    
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
    
            if (!$stmt->execute()) {
                throw new Exception('Error al ejecutar la consulta');
            }
    
            $result = $stmt->get_result();
            $projects = array();
    
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // Formatear la fecha para la salida JSON
                    $row['fecha_formatted'] = date('d/m/Y', strtotime($row['fecha']));
                    $projects[] = $row; // Añadir cada fila (proyecto) al array
                }
            }
    
            $stmt->close();
    
            // Devolver un array con los proyectos, la página actual y el total de páginas
            return array(
                'projects' => $projects,
                'current_page' => $page,
                'total_pages' => $total_pages,
                'total_records' => $total_records // Agregamos el total de registros para mayor utilidad
            );
        } catch (Exception $e) {
            error_log("Error en FilterProjects: " . $e->getMessage());
            return array(
                'projects' => [],
                'current_page' => 1,
                'total_pages' => 1,
                'total_records' => 0,
                'error' => 'Error al cargar los proyectos: ' . $e->getMessage()
            );
        }
    }
   
    public static function DeleteProject($idproyecto, $conexion)
    {
        // 1. Eliminar el proyecto
        $sql_delete_proyecto = "UPDATE proyectos
   SET activo = 0
 WHERE id = ?;";
        $stmt_delete_proyecto = $conexion->prepare($sql_delete_proyecto);
        $stmt_delete_proyecto->bind_param("i", $idproyecto);
        if (!$stmt_delete_proyecto->execute()) {
            throw new Exception('Error al eliminar projecto');
        }
        $stmt_delete_proyecto->close();
    }
}
