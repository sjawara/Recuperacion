    <?php
    require 'conexion.php';

    global $pedido; 
    $pedido = null; 

    $query = "SELECT * FROM Pedidos ORDER BY fecha_pedido DESC LIMIT 1";
    $resultado = $conn->query($query);
    if ($resultado) {
        $pedido = $resultado->fetch_assoc();
    } else {
        echo "Error al ejecutar la consulta: " . $conexion->error;
    }

    function fechaP(){
        global $pedido;
        return $pedido['fecha_pedido'];
    }

    function numP(){
        global $pedido;
        return $pedido['id_pedido'];
    }

    function nomCli(){
        global $pedido, $conn;
        
        // Escapar la variable $pedido['id_cliente'] para evitar inyección SQL
        $id_cliente = $conn->real_escape_string($pedido['id_cliente']);
    
        // Construir la consulta SQL con consultas preparadas
        $query = "SELECT nombre FROM Clientes WHERE id_cliente = ?";
        
        // Preparar la consulta
        $stmt = $conn->prepare($query);
        
        // Vincular parámetros y ejecutar la consulta
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        
        // Obtener resultado
        $resultado = $stmt->get_result();
    
        // Verificar si la consulta fue exitosa
        if($resultado) {
            // Obtener el primer resultado (asumiendo que solo esperas uno)
            $fila = $resultado->fetch_assoc();
            if ($fila) {
                // Imprimir el nombre
                echo $fila['nombre'];
            } else {
                echo "No se encontraron resultados para el cliente con ID {$pedido['id_cliente']}";
            }
        } else {
            echo "Error en la consulta: " . $conn->error;
        }
    }

    
    function datosCli(){
        global $pedido, $conn;
        $id_cliente = $pedido['id_cliente'];
    
        $query = "SELECT telefono, id_cliente, direccion, municipio, codip, pais FROM Clientes WHERE id_cliente = ?";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("i", $id_cliente);
            $stmt->execute();
            $resultado = $stmt->get_result();
            if ($resultado && $fila = $resultado->fetch_assoc()) {
                echo "Teléfono: " . $fila['telefono'] . "<br>";
                echo "ID Cliente: " . $fila['id_cliente'] . "<br>";
                echo "Dirección: " . $fila['direccion'] . "<br>";
                echo "Municipio: " . $fila['municipio'] . "<br>";
                echo "Código Postal: " . $fila['codip'] . "<br>";
                echo "País: " . $fila['pais'];
            } else {
                echo "No se encontraron datos para el cliente con ID $id_cliente";
            }
        } else {
            echo "Error en la preparación de la consulta: " . $conn->error;
        }
    }
    

    function datosEmp(){
        global $pedido, $conn;
        $query = "SELECT telefono, id_empresa, direccion, municipio, codip, pais FROM Empresa";
        $stmt = $conn->prepare($query);
        if ($stmt) {

            $stmt->execute();
            $resultado = $stmt->get_result();
            if ($resultado && $fila = $resultado->fetch_assoc()) {
                echo "Teléfono: " . $fila['telefono'] . "<br>";
                echo "CIF: " . $fila['id_empresa'] . "<br>";
                echo "Dirección: " . $fila['direccion'] . "<br>";
                echo "Municipio: " . $fila['municipio'] . "<br>";
                echo "Código Postal: " . $fila['codip'] . "<br>";
                echo "País: " . $fila['pais'];
            } else {
                echo "No se encontraron datos de la empresa";
            }
        } else {
            echo "Error en la preparación de la consulta: " . $conn->error;
        }
    }

    $totalSinIva = 0;
    $descuentoAcumulado = 0;
    
    function contenidoTab(){
        global $pedido, $conn, $totalSinIva, $descuentoAcumulado;
        $query = "SELECT d.id_detalle, d.id_producto, d.cantidad, d.precio_unitario, p.nombre, c.descuento 
        FROM Detalles_Pedido d  
        JOIN Productos p ON d.id_producto = p.id_producto 
        JOIN Pedidos pd ON d.id_pedido = pd.id_pedido 
        JOIN Clientes c ON pd.id_cliente = c.id_cliente
        WHERE d.id_pedido = 1;
        ";
        $result = $conn->query($query);
    
        if ($result && $result->num_rows > 0) {
            echo '<tbody>';
            // Recorremos los resultados y generamos las filas de la tabla
            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td class="border-b py-3 pl-3">' . $row['id_detalle'] . '.</td>';
                echo '<td class="border-b py-3 pl-2">' . $row['nombre'] . '</td>';
                echo '<td class="border-b py-3 pl-2 text-right">$' . $row['precio_unitario'] . '</td>';
                echo '<td class="border-b py-3 pl-2 text-center">' . $row['cantidad'] . '</td>';
                echo '<td class="border-b py-3 pl-2 text-center">' . $row['descuento'] . '%</td>';
                // Cálculo del subtotal
                $subtotal = $row['precio_unitario'] * $row['cantidad'];
                echo '<td class="border-b py-3 pl-2 text-right">$' . $subtotal . '</td>';
                $totalSinIva += $subtotal;
                // Cálculo del descuento acumulado
                $descuento = ($row['descuento'] / 100) * $subtotal;
                $descuentoAcumulado += $descuento;
                // Cálculo del total con descuento
                $total_con_descuento = $subtotal + $descuento;
                echo '<td class="border-b py-3 pl-2 pr-3 text-right">$' . number_format($total_con_descuento, 2) . '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
        } else {
            echo "No se encontraron detalles para el pedido con ID 123";
        }
    }
    
    function sinIvaTotal(){
        global $totalSinIva;
        echo number_format($totalSinIva, 2);
    }

    function ivaTotal(){
        global $descuentoAcumulado;
        echo number_format($descuentoAcumulado, 2);
    }

    function totalFactura(){
        global $descuentoAcumulado, $totalSinIva;
        $total = $descuentoAcumulado + $totalSinIva;
        echo number_format($total, 2);
    }
    ?>