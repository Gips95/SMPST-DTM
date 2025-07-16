<?php
class Statistics {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // Estadísticas principales
    public function getMainStats($filters = []) {
        // Filtros base para proyectos
        $whereProyectos = "p.activo = 1";
        $params = [];
        $types = "";
    
        // Construir condiciones dinámicas
        if (!empty($filters['fecha_inicio'])) {
            $year = date('Y', strtotime($filters['fecha_inicio']));
            $whereProyectos .= " AND YEAR(p.fecha) = ?";
            $params[] = $year;
            $types .= "s";
        }
    
        if (!empty($filters['linea_investigacion'])) {
            $whereProyectos .= " AND p.linea_investigacion = ?";
            $params[] = $filters['linea_investigacion'];
            $types .= "s";
        }
    
        // Consultas individuales explícitas
        $queries = [
            'total_proyectos' => "
                SELECT COUNT(*) AS total 
                FROM proyectos p 
                WHERE $whereProyectos
            ",
    
            'lineas_unicas' => "
                SELECT COUNT(DISTINCT linea_investigacion) AS total 
                FROM proyectos p 
                WHERE $whereProyectos
            ",
    
            'total_archivos' => "
                SELECT COUNT(a.id) AS total 
                FROM archivos a
                INNER JOIN proyectos p ON a.proyecto_id = p.id 
                WHERE $whereProyectos
            ",
    
            'espacio_total' => "
                SELECT SUM(a.size) AS total 
                FROM archivos a
                INNER JOIN proyectos p ON a.proyecto_id = p.id 
                WHERE $whereProyectos
            ",
    
            'total_estudiantes' => "
                SELECT COUNT(*) AS total 
                FROM usuarios 
                WHERE rol = 'estudiante'
            ",
    
            'total_profesores' => "
                SELECT COUNT(*) AS total 
                FROM usuarios 
                WHERE rol = 'profesor'
            "
        ];
    
        $results = [];
    
        // Ejecutar cada consulta individualmente
        foreach ($queries as $key => $sql) {
            $stmt = $this->conexion->prepare($sql);
            
            if ($key != 'total_estudiantes' && $key != 'total_profesores') {
                // Vincular parámetros solo para consultas que los necesitan
                if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                }
            }
    
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $results[$key] = $result['total'] ?? 0;
        }
    
        return $results;
    }
    // Distribución de proyectos por línea de investigación
    public function getProjectsByLine($filters = []) {
        // Base
        $where = ["p.activo = 1"];
        $params = [];
        $types  = "";
    
        // Filtro de año (si existe)
        if (!empty($filters['fecha_inicio'])) {
            $year = date('Y', strtotime($filters['fecha_inicio']));
            $where[] = "YEAR(p.fecha) = ?";
            $params[] = $year;
            $types .= "i";
        }
    
        // Filtro de línea (si lo envías)
        if (!empty($filters['linea_investigacion'])) {
            $where[] = "p.linea_investigacion = ?";
            $params[] = $filters['linea_investigacion'];
            $types .= "s";
        }
    
        $sql = "SELECT linea_investigacion, COUNT(*) AS total
                FROM proyectos p
                WHERE " . implode(" AND ", $where) . "
                GROUP BY linea_investigacion
                ORDER BY total DESC
                LIMIT 5";
    
        $stmt = $this->conexion->prepare($sql);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Evolución temporal de proyectos
   public function getProjectsTimeline($filters = [], $interval = 'month') {
    $format = $interval === 'year' ? '%Y' : '%Y-%m';

    $where = ["p.activo = 1"];
    $params = [];
    $types  = "";

    // Filtrar por año si viene
    if (!empty($filters['fecha_inicio'])) {
        $year = date('Y', strtotime($filters['fecha_inicio']));
        $where[] = "YEAR(p.fecha) = ?";
        $params[] = $year;
        $types .= "i";
    }

    // Construimos consulta
    $sql = "SELECT DATE_FORMAT(fecha, '$format') AS periodo,
                   COUNT(*) AS total_proyectos
            FROM proyectos p
            WHERE " . implode(" AND ", $where) . "
            GROUP BY periodo
            ORDER BY periodo DESC";

    $stmt = $this->conexion->prepare($sql);
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
    // Archivos por tipo
    public function getFilesByType($filters = []) {
        $where = ["1=1"];
        $params = [];
        $types  = "";
    
        // Aplica el filtro de año sobre la fecha del proyecto relacionado
        if (!empty($filters['fecha_inicio'])) {
            $year = date('Y', strtotime($filters['fecha_inicio']));
            $where[] = "YEAR(p.fecha) = ?";
            $params[] = $year;
            $types .= "i";
        }
    
        $sql = "SELECT a.tipo,
                       COUNT(*) AS cantidad,
                       AVG(a.size) AS promedio_tamano
                FROM archivos a
                INNER JOIN proyectos p ON a.proyecto_id = p.id
                WHERE " . implode(" AND ", $where) . "
                GROUP BY a.tipo";
    
        $stmt = $this->conexion->prepare($sql);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    private function buildWhere($conditions) {
        return empty($conditions) ? "" : " WHERE ".implode(" AND ", $conditions);
    }
}
?>