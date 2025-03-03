<?php
require_once "Database.php";

class Producto {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function obtenerProductos($nombre = null, $precio_mayor_que = null) {
        $sql = "SELECT * FROM productos WHERE 1=1";

        if ($nombre) {
            $sql .= " AND nombre LIKE '%" . $this->conn->real_escape_string($nombre) . "%'";
        }
        if ($precio_mayor_que != null) {
            $sql .= " AND precio > " . (float) $this->conn->real_escape_string($precio_mayor_que);
        }

        $resultado = $this->conn->query($sql);
        return $resultado;
    }

    public function obtenerProductoPorId($id) {
        $id = (int) $id;
        $sql = "SELECT * FROM productos WHERE id = $id";
        return $this->conn->query($sql);
    }

    public function crearProducto($nombre, $precio, $stock) {
        $nombre = $this->conn->real_escape_string($nombre);
        $precio = (float) $precio;
        $stock = (int) $stock;
        
        $sql = "INSERT INTO productos (nombre, precio, stock) VALUES ('$nombre', $precio, $stock)";
        
        if ($this->conn->query($sql)) {
            return $this->conn->insert_id;
        } else {
            return false;
        }
    }

    public function actualizarProducto($id, $nombre, $precio, $stock) {
        $id = (int) $id;
        $nombre = $this->conn->real_escape_string($nombre);
        $precio = (float) $precio;
        $stock = (int) $stock;
        
        $sql = "UPDATE productos SET nombre = '$nombre', precio = $precio, stock = $stock WHERE id = $id";
        
        return $this->conn->query($sql);
    }

    public function eliminarProducto($id) {
        $id = (int) $id;
        $sql = "DELETE FROM productos WHERE id = $id";
        
        return $this->conn->query($sql);
    }
}
?>