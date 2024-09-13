<?php
// TODO: Clase de Producto
require_once('../config/config.php');

class Producto
{
    public function todos() // select * from productos
    {
        $con = new ClaseConectar();
        $con = $con->ProcedimientoParaConectar();
        $cadena = " SELECT
            productos.idProductos,
            productos.Codigo_Barras,
            productos.Nombre_Producto,
            productos.Graba_IVA,
            unidad_medida.Detalle AS Unidad_Medida,
            iva.Detalle AS IVA_Detalle,
            kardex.Cantidad,
            kardex.Valor_Compra,
            kardex.Valor_Venta,
            kardex.Fecha_Transaccion,
            kardex.Tipo_Transaccion,
            kardex.idKardex
        FROM
            productos
            INNER JOIN kardex ON  productos.idProductos = kardex.Productos_idProductos
            INNER JOIN iva ON kardex.IVA_idIVA = iva.idIVA
            INNER JOIN proveedores ON kardex.Proveedores_idProveedores = proveedores.idProveedores
            INNER JOIN unidad_medida ON
                kardex.Unidad_Medida_idUnidad_Medida = unidad_medida.idUnidad_Medida AND
                kardex.Unidad_Medida_idUnidad_Medida1 = unidad_medida.idUnidad_Medida AND
                kardex.Unidad_Medida_idUnidad_Medida2 = unidad_medida.idUnidad_Medida
        WHERE
            kardex.Estado = 1
        ORDER BY
            productos.Nombre_Producto";
        $datos = mysqli_query($con, $cadena);
        $con->close();
        return $datos;
    }

    public function uno($idProductos) // select * from productos where id = $idProductos
    {
        $con = new ClaseConectar();
        $con = $con->ProcedimientoParaConectar();
        $cadena = "SELECT p.*, u.Detalle as Unidad_Medida, i.Detalle as IVA_Detalle 
                   FROM `Productos` p 
                   INNER JOIN Unidad_Medida u ON p.idProductos = u.idUnidad_Medida 
                   INNER JOIN IVA i ON p.Graba_IVA = i.idIVA 
                   WHERE p.idProductos = $idProductos";
        $datos = mysqli_query($con, $cadena);
        $con->close();
        return $datos;
    }

    public function insertar($Codigo_Barras, $Nombre_Producto, $Graba_IVA, $Unidad_Medida_idUnidad_Medida, $IVA_idIVA, $Cantidad, $Valor_Compra, $Valor_Venta, $Proveedores_idProveedores)
    {
        try {
            $con = new ClaseConectar();
            $con = $con->ProcedimientoParaConectar();

            // Insertar el producto
            $cadenaProducto = "INSERT INTO `Productos`(`Codigo_Barras`, `Nombre_Producto`, `Graba_IVA`) 
                               VALUES ('$Codigo_Barras', '$Nombre_Producto', '$Graba_IVA')";

            if (mysqli_query($con, $cadenaProducto)) {
                $productoId = $con->insert_id; // Obtener el ID del producto recién creado

                // Insertar el Kardex asociado al producto
                $cadenaKardex = "INSERT INTO `Kardex`(`Estado`, `Fecha_Transaccion`, `Cantidad`, `Valor_Compra`, `Valor_Venta`, `Unidad_Medida_idUnidad_Medida`, `Unidad_Medida_idUnidad_Medida1`, `Unidad_Medida_idUnidad_Medida2`, `IVA`, `IVA_idIVA`, `Proveedores_idProveedores`, `Productos_idProductos`, `Tipo_Transaccion`)
                                 VALUES (1, NOW(), '$Cantidad', '$Valor_Compra', '$Valor_Venta', '$Unidad_Medida_idUnidad_Medida', '$Unidad_Medida_idUnidad_Medida', '$Unidad_Medida_idUnidad_Medida', '$IVA_idIVA', '$IVA_idIVA', '$Proveedores_idProveedores', '$productoId', 1)";

                if (mysqli_query($con, $cadenaKardex)) {
                    return $productoId; // Éxito, devolver el ID del producto
                } else {
                    return $con->error; // Error en el Kardex
                }
            } else {
                return $con->error; // Error en el producto
            }
        } catch (Exception $th) {
            return $th->getMessage();
        } finally {
            $con->close();
        }
    }

    public function actualizar($idProductos, $Codigo_Barras, $Nombre_Producto, $Graba_IVA) // update productos set ... where id = $idProductos
    {
        try {
            $con = new ClaseConectar();
            $con = $con->ProcedimientoParaConectar();
            $cadena = "UPDATE `Productos` SET 
                       `Codigo_Barras`='$Codigo_Barras',
                       `Nombre_Producto`='$Nombre_Producto',
                       `Graba_IVA`='$Graba_IVA'
                       WHERE `idProductos` = $idProductos";
            if (mysqli_query($con, $cadena)) {
                return $idProductos; // Éxito, devolver el ID actualizado
            } else {
                return $con->error;
            }
        } catch (Exception $th) {
            return $th->getMessage();
        } finally {
            $con->close();
        }
    }

    public function eliminar($idProductos) // delete from productos where id = $idProductos
    {
        try {
            $con = new ClaseConectar();
            $con = $con->ProcedimientoParaConectar();
            $cadena = "UPDATE `kardex` SET `Estado`=0 WHERE `Productos_idProductos`=$idProductos";
            if (mysqli_query($con, $cadena)) {
                return 1; // Éxito
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
