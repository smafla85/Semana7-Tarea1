<?php
// TODO: Clase de DetalleFactura Tienda Cel@g
require_once('../config/config.php');

class DetalleFactura
{
    public function todos($idFactura) // select * from detalle_factura where Factura_idFactura = $idFactura
    {
        $con = new ClaseConectar();
        $con = $con->ProcedimientoParaConectar();
        $cadena = "SELECT df.*, p.Nombre_Producto FROM `detalle_factura` df 
                   INNER JOIN productos p ON df.Productos_idProductos = p.idProductos 
                   WHERE df.Factura_idFactura = $idFactura";
        $datos = mysqli_query($con, $cadena);
        $con->close();
        return $datos;
    }

    public function uno($idDetalleFactura) // select * from detalle_factura where id = $idDetalleFactura
    {
        $con = new ClaseConectar();
        $con = $con->ProcedimientoParaConectar();
        $cadena = "SELECT df.*, p.Nombre_Producto FROM `detalle_factura` df 
                   INNER JOIN productos p ON df.Productos_idProductos = p.idProductos 
                   WHERE df.idDetalleFactura = $idDetalleFactura";
        $datos = mysqli_query($con, $cadena);
        $con->close();
        return $datos;
    }

    public function insertar($Factura_idFactura, $Productos_idProductos, $Cantidad, $Precio_Venta, $Subtotal) 
    {
        try {
            $con = new ClaseConectar();
            $con = $con->ProcedimientoParaConectar();
            $cadena = "INSERT INTO `detalle_factura`(`Factura_idFactura`, `Productos_idProductos`, `Cantidad`, `Precio_Venta`, `Subtotal`) 
                       VALUES ('$Factura_idFactura', '$Productos_idProductos', '$Cantidad', '$Precio_Venta', '$Subtotal')";
            if (mysqli_query($con, $cadena)) {
                return $con->insert_id; // Return the inserted ID
            } else {
                return $con->error;
            }
        } catch (Exception $th) {
            return $th->getMessage();
        } finally {
            $con->close();
        }
    }

    public function actualizar($idDetalleFactura, $Factura_idFactura, $Productos_idProductos, $Cantidad, $Precio_Venta, $Subtotal)
    {
        try {
            $con = new ClaseConectar();
            $con = $con->ProcedimientoParaConectar();
            $cadena = "UPDATE `detalle_factura` SET 
                       `Factura_idFactura`='$Factura_idFactura',
                       `Productos_idProductos`='$Productos_idProductos',
                       `Cantidad`='$Cantidad',
                       `Precio_Venta`='$Precio_Venta',
                       `Subtotal`='$Subtotal'
                       WHERE `idDetalleFactura` = $idDetalleFactura";
            if (mysqli_query($con, $cadena)) {
                return $idDetalleFactura; // Return the updated ID
            } else {
                return $con->error;
            }
        } catch (Exception $th) {
            return $th->getMessage();
        } finally {
            $con->close();
        }
    }

    public function eliminar($idDetalleFactura)
    {
        try {
            $con = new ClaseConectar();
            $con = $con->ProcedimientoParaConectar();
            $cadena = "DELETE FROM `detalle_factura` WHERE `idDetalleFactura`= $idDetalleFactura";
            if (mysqli_query($con, $cadena)) {
                return 1; // Success
            } else {
                return $con->error;
            }
        } catch (Exception $th) {
            return $th->getMessage();
        } finally {
            $con->close();
        }
    }
}