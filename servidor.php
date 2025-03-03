<?php
require_once "Producto.php";

class ProductoService {
    private $tokenCorrecto = "123456";
    private $producto;

    public function __construct() {
        $this->producto = new Producto();
    }

    public function validarAcceso() {
        if (!isset($_GET['token']) || $_GET['token'] !== $this->tokenCorrecto) {
            $this->enviarError("Acceso no autorizado");
        }
    }

    public function procesarSolicitud() {
        $accion = isset($_GET['accion']) ? $_GET['accion'] : 'leer';
        
        switch ($accion) {
            case 'leer':
                $this->obtenerProductos();
                break;
            case 'leer_uno':
                $this->obtenerProductoPorId();
                break;
            case 'crear':
                $this->crearProducto();
                break;
            case 'actualizar':
                $this->actualizarProducto();
                break;
            case 'eliminar':
                $this->eliminarProducto();
                break;
            default:
                $this->enviarError("Acción no válida");
        }
    }

    public function obtenerProductos() {
        $nombre = isset($_GET['nombre']) ? $_GET['nombre'] : null;
        $precio_mayor_que = isset($_GET['precio_mayor_que']) ? $_GET['precio_mayor_que'] : null;

        $resultado = $this->producto->obtenerProductos($nombre, $precio_mayor_que);
        $this->generarXML($resultado);
    }
    
    public function obtenerProductoPorId() {
        if (!isset($_GET['id'])) {
            $this->enviarError("Se requiere un ID");
        }
        
        $id = $_GET['id'];
        $resultado = $this->producto->obtenerProductoPorId($id);
        $this->generarXML($resultado);
    }
    
    public function crearProducto() {
        if (!isset($_POST['nombre']) || !isset($_POST['precio']) || !isset($_POST['stock'])) {
            $this->enviarError("Faltan datos requeridos");
        }
        
        $nombre = $_POST['nombre'];
        $precio = $_POST['precio'];
        $stock = $_POST['stock'];
        
        $resultado = $this->producto->crearProducto($nombre, $precio, $stock);
        
        if ($resultado) {
            $this->generarRespuestaExito("Producto creado con éxito", $resultado);
        } else {
            $this->enviarError("Error al crear el producto");
        }
    }
    
    public function actualizarProducto() {
        if (!isset($_POST['id']) || !isset($_POST['nombre']) || !isset($_POST['precio']) || !isset($_POST['stock'])) {
            $this->enviarError("Faltan datos requeridos");
        }
        
        $id = $_POST['id'];
        $nombre = $_POST['nombre'];
        $precio = $_POST['precio'];
        $stock = $_POST['stock'];
        
        $resultado = $this->producto->actualizarProducto($id, $nombre, $precio, $stock);
        
        if ($resultado) {
            $this->generarRespuestaExito("Producto actualizado con éxito");
        } else {
            $this->enviarError("Error al actualizar el producto");
        }
    }
    
    public function eliminarProducto() {
        if (!isset($_GET['id'])) {
            $this->enviarError("Se requiere un ID");
        }
        
        $id = $_GET['id'];
        $resultado = $this->producto->eliminarProducto($id);
        
        if ($resultado) {
            $this->generarRespuestaExito("Producto eliminado con éxito");
        } else {
            $this->enviarError("Error al eliminar el producto");
        }
    }

    private function enviarError($mensaje, $dom = null, $root = null) {
        if (!$dom) {
            header("Content-Type: application/xml; charset=UTF-8");
            $dom = new DOMDocument("1.0", "UTF-8");
            $dom->formatOutput = true;
            $root = $dom->createElement("error");
            $dom->appendChild($root);
        }

        $mensajeNode = $dom->createElement("mensaje", $mensaje);
        $root->appendChild($mensajeNode);

        echo $dom->saveXML();
        exit;
    }
    
    private function generarRespuestaExito($mensaje, $id = null) {
        header("Content-Type: application/xml; charset=UTF-8");
        
        $dom = new DOMDocument("1.0", "UTF-8");
        $dom->formatOutput = true;
        $root = $dom->createElement("respuesta");
        $dom->appendChild($root);
        
        $estadoNode = $dom->createElement("estado", "exito");
        $root->appendChild($estadoNode);
        
        $mensajeNode = $dom->createElement("mensaje", $mensaje);
        $root->appendChild($mensajeNode);
        
        if ($id !== null) {
            $idNode = $dom->createElement("id", $id);
            $root->appendChild($idNode);
        }
        
        echo $dom->saveXML();
    }

    private function generarXML($resultado) {
        header("Content-Type: application/xml; charset=UTF-8");

        $dom = new DOMDocument("1.0", "UTF-8");
        $dom->formatOutput = true;
        $root = $dom->createElement("productos");
        $dom->appendChild($root);

        if ($resultado->num_rows > 0) {
            while ($fila = $resultado->fetch_assoc()) {
                $producto = $dom->createElement("producto");
                $producto->setAttribute("id", $fila["id"]);
                $producto->setAttribute("nombre", $fila["nombre"]);
                $producto->setAttribute("precio", $fila["precio"]);
                $producto->setAttribute("stock", $fila["stock"]);
                $root->appendChild($producto);
            }
        } else {
            $this->enviarError("No se encontraron productos", $dom, $root);
        }

        echo $dom->saveXML();
    }
}

$service = new ProductoService();
$service->validarAcceso();
$service->procesarSolicitud();
?>