<?php
class ClienteProducto {
    private $token;
    private $apiUrl;

    public function __construct($url, $token) {
        $this->apiUrl = $url;
        $this->token = $token;
    }

    public function obtenerProductos($nombre = null, $precio_mayor_que = null) {
        $url = $this->apiUrl . "?token=" . $this->token . "&accion=leer";

        if (!empty($nombre)) {
            $url .= "&nombre=" . urlencode($nombre);
        }
        if (!empty($precio_mayor_que)) {
            $url .= "&precio_mayor_que=" . urlencode($precio_mayor_que);
        }

        return $this->procesarXML($url);
    }
    
    public function obtenerProductoPorId($id) {
        $url = $this->apiUrl . "?token=" . $this->token . "&accion=leer_uno&id=" . urlencode($id);
        return $this->procesarXML($url);
    }
    
    public function crearProducto($nombre, $precio, $stock) {
        $url = $this->apiUrl . "?token=" . $this->token . "&accion=crear";
        
        $datos = array(
            'nombre' => $nombre,
            'precio' => $precio,
            'stock' => $stock
        );
        
        return $this->procesarPOST($url, $datos);
    }
    
    public function actualizarProducto($id, $nombre, $precio, $stock) {
        $url = $this->apiUrl . "?token=" . $this->token . "&accion=actualizar";
        
        $datos = array(
            'id' => $id,
            'nombre' => $nombre,
            'precio' => $precio,
            'stock' => $stock
        );
        
        return $this->procesarPOST($url, $datos);
    }
    
    public function eliminarProducto($id) {
        $url = $this->apiUrl . "?token=" . $this->token . "&accion=eliminar&id=" . urlencode($id);
        return $this->procesarXML($url);
    }

    private function procesarXML($url) {
        $xml = simplexml_load_file($url);

        if (!$xml) {
            die("Error al cargar XML");
        }

        return $xml;
    }
    
    private function procesarPOST($url, $datos) {
        $opciones = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($datos)
            )
        );
        
        $contexto = stream_context_create($opciones);
        $resultado = file_get_contents($url, false, $contexto);
        
        if ($resultado === FALSE) {
            die("Error al realizar solicitud POST");
        }
        
        return simplexml_load_string($resultado);
    }
}

// Inicialización del cliente
$cliente = new ClienteProducto("http://localhost/webservices/tema3/servidor.php", "123456");

// Procesar formularios para CRUD
$mensaje = "";
$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

// Procesar acción de crear
if ($accion == 'crear' && isset($_POST['nombre']) && isset($_POST['precio']) && isset($_POST['stock'])) {
    $resultado = $cliente->crearProducto($_POST['nombre'], $_POST['precio'], $_POST['stock']);
    if (isset($resultado->estado) && $resultado->estado == 'exito') {
        $mensaje = "Producto creado con éxito.";
    }
}

// Procesar acción de actualizar
if ($accion == 'actualizar' && isset($_POST['id']) && isset($_POST['nombre']) && isset($_POST['precio']) && isset($_POST['stock'])) {
    $resultado = $cliente->actualizarProducto($_POST['id'], $_POST['nombre'], $_POST['precio'], $_POST['stock']);
    if (isset($resultado->estado) && $resultado->estado == 'exito') {
        $mensaje = "Producto actualizado con éxito.";
    }
}

// Procesar acción de eliminar
if ($accion == 'eliminar' && isset($_POST['id_eliminar'])) {
    $resultado = $cliente->eliminarProducto($_POST['id_eliminar']);
    if (isset($resultado->estado) && $resultado->estado == 'exito') {
        $mensaje = "Producto eliminado con éxito.";
    }
}

// Procesar filtros y obtener productos
$filtro_nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';
$filtro_precio = isset($_POST['precio_mayor_que']) ? $_POST['precio_mayor_que'] : '';

$xml = $cliente->obtenerProductos($filtro_nombre, $filtro_precio);

// Obtener producto para editar
$producto_editar = null;
if (isset($_GET['editar'])) {
    $producto_editar = $cliente->obtenerProductoPorId($_GET['editar']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obtener Productos XML</title>
    <style>
        table { width: 60%; border-collapse: collapse; margin: 20px auto; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        form { text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>

    <?php if (!empty($mensaje)): ?>
    <p style="text-align: center; color: green;"><?php echo $mensaje; ?></p>
    <?php endif; ?>

    <h2 style="text-align: center;">Buscar Productos</h2>

    <form method="post">
        Nombre: <input type="text" name="nombre" value="<?php echo htmlspecialchars($filtro_nombre); ?>">
        Precio mayor que: <input type="number" name="precio_mayor_que" value="<?php echo htmlspecialchars($filtro_precio); ?>">
        <button type="submit">Buscar</button>
    </form>

    <?php if ($producto_editar): ?>
    <h2 style="text-align: center;">Editar Producto</h2>
    <?php else: ?>
    <h2 style="text-align: center;">Crear Producto</h2>
    <?php endif; ?>

    <form method="post" style="width: 60%; margin: 0 auto;">
        <input type="hidden" name="accion" value="<?php echo $producto_editar ? 'actualizar' : 'crear'; ?>">
        <?php if ($producto_editar): ?>
        <input type="hidden" name="id" value="<?php echo $producto_editar->producto['id']; ?>">
        <?php endif; ?>
        
        <table>
            <tr>
                <td>Nombre:</td>
                <td><input type="text" name="nombre" value="<?php echo $producto_editar ? $producto_editar->producto['nombre'] : ''; ?>" required></td>
            </tr>
            <tr>
                <td>Precio:</td>
                <td><input type="number" name="precio" step="0.01" value="<?php echo $producto_editar ? $producto_editar->producto['precio'] : ''; ?>" required></td>
            </tr>
            <tr>
                <td>Stock:</td>
                <td><input type="number" name="stock" value="<?php echo $producto_editar ? $producto_editar->producto['stock'] : ''; ?>" required></td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center;">
                    <button type="submit"><?php echo $producto_editar ? 'Actualizar' : 'Crear'; ?></button>
                    <?php if ($producto_editar): ?>
                    <a href="cliente.php">Cancelar</a>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </form>

    <h2 style="text-align: center;">Lista de Productos</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Precio</th>
            <th>Stock</th>
            <th>Acciones</th>
        </tr>
        <?php foreach ($xml->producto as $producto): ?>
        <tr>
            <td><?php echo $producto['id']; ?></td>
            <td><?php echo $producto['nombre']; ?></td>
            <td><?php echo $producto['precio']; ?></td>
            <td><?php echo $producto['stock']; ?></td>
            <td>
                <a href="cliente.php?editar=<?php echo $producto['id']; ?>">Editar</a>
                <form method="post" style="display: inline; margin: 0;">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id_eliminar" value="<?php echo $producto['id']; ?>">
                    <button type="submit" onclick="return confirm('¿Está seguro de eliminar este producto?');">Eliminar</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

</body>
</html>