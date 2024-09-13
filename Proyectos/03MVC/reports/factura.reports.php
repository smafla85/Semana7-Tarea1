<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
$method = $_SERVER["REQUEST_METHOD"];
if ($method == "OPTIONS") {
    die();
}

require('fpdf/fpdf.php');
require_once('../config/config.php');
require_once("../models/factura.model.php");
require_once("../models/clientes.model.php");

class PDF extends FPDF
{
    function Header()
    {
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, utf8_decode('FACTURA'), 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

// Recibir datos del frontend
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

if (!$data) {
    die('Error: No se recibieron datos válidos');
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetMargins(10, 10, 10);
$pdf->AliasNbPages();

// Datos de la empresa 
$empresaNombre = utf8_decode("Tienda XYZ");
$empresaRUC = "1234567890001";
$empresaDireccion = utf8_decode("Av. 10 de Agosto y Colón, Quito, Ecuador");
$empresaTelefono = "593999017409";

// Datos de la factura
$facturaNumero = "001-001-" . str_pad(rand(1, 999999), 6, "0", STR_PAD_LEFT); 
$facturaFecha = date("d/m/Y");

// Información del cliente
$clienteData = $data['cliente'];

// Información de la empresa
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 5, $empresaNombre, 0, 1);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(0, 5, 'RUC: ' . $empresaRUC, 0, 1);
$pdf->Cell(0, 5, 'Direccion: ' . $empresaDireccion, 0, 1);
$pdf->Cell(0, 5, 'Telefono: ' . $empresaTelefono, 0, 1);

// Información de la factura
$pdf->Ln(2);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(0, 5, 'Factura No.: ' . $facturaNumero . '     Fecha: ' . $facturaFecha, 0, 1);

// Información del cliente
$pdf->Ln(2);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(0, 5, 'Datos del Cliente:', 0, 1);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(0, 4, 'Nombre: ' . utf8_decode($clienteData['Nombres']), 0, 1);
$pdf->Cell(0, 4, 'CI/RUC: ' . $clienteData['Cedula'] . '     Tel: ' . $clienteData['Telefono'], 0, 1);
$pdf->Cell(0, 4, 'Direccion: ' . utf8_decode($clienteData['Direccion']), 0, 1);
$pdf->Cell(0, 4, 'Correo: ' . $clienteData['Correo'], 0, 1);

// Detalles de la factura
$pdf->Ln(2);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(20, 7, "Cant.", 1, 0, 'C');
$pdf->Cell(85, 7, "Descripcion", 1, 0, 'C');
$pdf->Cell(40, 7, "Precio Unit.", 1, 0, 'C');
$pdf->Cell(40, 7, "Total", 1, 1, 'C');

$pdf->SetFont('Arial', '', 8);

$subtotal = 0;
$iva = 0;

foreach ($data['productos'] as $producto) {
    $pdf->Cell(20, 6, $producto['Cantidad'], 1, 0, 'C');
    $pdf->Cell(85, 6, utf8_decode($producto['Nombre_Producto']), 1, 0, 'L');
    $pdf->Cell(40, 6, '$' . number_format($producto['Valor_Venta'], 2), 1, 0, 'R');
    $pdf->Cell(40, 6, '$' . number_format($producto['Total'], 2), 1, 1, 'R');

    $subtotal += $producto['Total'];
    if ($producto['Graba_IVA']) {
        $iva += $producto['Total'] * 0.15; // IVA del 15%
    }
}

$total = $subtotal + $iva;

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(145, 6, 'Subtotal', 1, 0, 'R');
$pdf->Cell(40, 6, '$' . number_format($subtotal, 2), 1, 1, 'R');
$pdf->Cell(145, 6, 'IVA (15%)', 1, 0, 'R');
$pdf->Cell(40, 6, '$' . number_format($iva, 2), 1, 1, 'R');
$pdf->Cell(145, 6, 'Total', 1, 0, 'R');
$pdf->Cell(40, 6, '$' . number_format($total, 2), 1, 1, 'R');

$pdf->Output('I', 'factura_' . $facturaNumero . '.pdf', true);
?>