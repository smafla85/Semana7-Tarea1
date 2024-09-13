import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { FormControl, FormGroup, FormsModule, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { IFactura } from 'src/app/Interfaces/factura';
import { ICliente } from 'src/app/Interfaces/icliente';
import { ClientesService } from 'src/app/Services/clientes.service';
import { FacturaService } from 'src/app/Services/factura.service';
import jsPDF from 'jspdf';
import 'jspdf-autotable';

@Component({
  selector: 'app-nuevafactura',
  standalone: true,
  imports: [FormsModule, ReactiveFormsModule, CommonModule],
  templateUrl: './nuevafactura.component.html',
  styleUrl: './nuevafactura.component.scss'
})
export class NuevafacturaComponent implements OnInit {
  titulo = 'Nueva Factura';
  listaClientes: ICliente[] = [];
  listaClientesFiltrada: ICliente[] = [];
  totalapagar: number = 0;
  frm_factura: FormGroup;
  productoelejido: any[] = [
    {
      Descripcion: 'Producto 1',
      Cantidad: 2,
      Precio: 1000,
      Subtotal: 2000,
      IVA: 300,
      Total: 2300
    },
    {
      Descripcion: 'Producto 2',
      Cantidad: 2,
      Precio: 1000,
      Subtotal: 2000,
      IVA: 300,
      Total: 2300
    },
    {
      Descripcion: 'Producto 3',
      Cantidad: 2,
      Precio: 1000,
      Subtotal: 2000,
      IVA: 300,
      Total: 2300
    }
  ];

  constructor(
    private clientesServicios: ClientesService,
    private facturaService: FacturaService,
    private navegacion: Router,
    private modal: NgbModal
  ) {}

  ngOnInit(): void {
    this.frm_factura = new FormGroup({
      Fecha: new FormControl('', Validators.required),
      Sub_total: new FormControl('0', Validators.required),
      Sub_total_iva: new FormControl('0', Validators.required),
      Valor_IVA: new FormControl('0.15', Validators.required),
      Clientes_idClientes: new FormControl('', Validators.required)
    });

    this.clientesServicios.todos().subscribe({
      next: (data) => {
        this.listaClientes = data;
        this.listaClientesFiltrada = data;
      },
      error: (e) => {
        console.log(e);
      }
    });

    // Suscribirse a cambios en Sub_total y Valor_IVA
    this.frm_factura.get('Sub_total')?.valueChanges.subscribe(() => this.calculos());
    this.frm_factura.get('Valor_IVA')?.valueChanges.subscribe(() => this.calculos());

    // Calcular totales iniciales
    this.actualizarTotales();
  }

  actualizarTotales() {
    const nuevoSubTotal = this.productoelejido.reduce((total, producto) => total + producto.Subtotal, 0);
    const nuevoIVATotal = this.productoelejido.reduce((total, producto) => total + producto.IVA, 0);

    this.frm_factura.get('Sub_total')?.setValue(nuevoSubTotal.toFixed(2));
    this.frm_factura.get('Sub_total_iva')?.setValue(nuevoIVATotal.toFixed(2));

    this.calculos();
  }

  calculos() {
    let sub_total = parseFloat(this.frm_factura.get('Sub_total')?.value) || 0;
    let iva = parseFloat(this.frm_factura.get('Sub_total_iva')?.value) || 0;
    this.totalapagar = sub_total + iva;
    this.frm_factura.get('Sub_total')?.setValue(sub_total.toFixed(2));
    this.frm_factura.get('Sub_total_iva')?.setValue(iva.toFixed(2));
  }

  grabar() {
    const doc = new jsPDF();
    
    // Configuración inicial
    doc.setFontSize(18);
    doc.text('Empresa XYZ', 14, 20);
    
    doc.setFontSize(10);
    doc.text('RUC: 1234567890', 14, 30);
    doc.text('Dirección: Calle Falsa 123, Quito, Ecuador', 14, 35);
    doc.text('Teléfono: +593 999 999 999', 14, 40);
    doc.text('Email: info@empresaxyz.com', 14, 45);

    // Información de la factura
    doc.setFontSize(14);
    doc.text('Factura', 150, 20);
    doc.setFontSize(10);
    doc.text(`No. 001-001-000000001`, 150, 30);
    doc.text(`Fecha de Emisión: ${this.frm_factura.get('Fecha')?.value}`, 150, 35);

    // Información del cliente
    doc.setFontSize(12);
    doc.text('Datos del Cliente', 14, 60);
    doc.setFontSize(10);
    const clienteSeleccionado = this.listaClientes.find(c => c.idClientes === this.frm_factura.get('Clientes_idClientes')?.value);
    if (clienteSeleccionado) {
      doc.text(`Nombre: ${clienteSeleccionado.Nombres}`, 14, 70);
      doc.text(`Cédula/RUC: ${clienteSeleccionado.Cedula}`, 14, 75);
      doc.text(`Dirección: ${clienteSeleccionado.Direccion}`, 14, 80);
      doc.text(`Teléfono: ${clienteSeleccionado.Telefono}`, 14, 85);
    }

    // Tabla de productos
    const columnas = ['Descripción', 'Cantidad', 'Precio Unit.', 'Subtotal', 'IVA', 'Total'];
    const filas = this.productoelejido.map(producto => [
      producto.Descripcion,
      producto.Cantidad,
      producto.Precio.toFixed(2),
      producto.Subtotal.toFixed(2),
      producto.IVA.toFixed(2),
      producto.Total.toFixed(2)
    ]);

    (doc as any).autoTable({
      head: [columnas],
      body: filas,
      startY: 95,
      theme: 'grid'
    });

    const finalY = (doc as any).lastAutoTable.finalY || 95;

    // Totales
    doc.text(`Subtotal: $${this.frm_factura.get('Sub_total')?.value}`, 140, finalY + 10);
    doc.text(`IVA (15%): $${this.frm_factura.get('Sub_total_iva')?.value}`, 140, finalY + 20);
    doc.text(`Total a pagar: $${this.totalapagar.toFixed(2)}`, 140, finalY + 30);

    // Información adicional
    doc.setFontSize(10);
    doc.text('Forma de pago: Transferencia Bancaria', 14, finalY + 50);
    doc.text('Cuenta Bancaria: Banco Pichincha, Cta: 123456789', 14, finalY + 55);
    doc.text('Nota: Gracias por su compra.', 14, finalY + 60);

    doc.save('factura.pdf');

    // Guardar la factura en la base de datos
    let factura: IFactura = {
      Fecha: this.frm_factura.get('Fecha')?.value,
      Sub_total: this.frm_factura.get('Sub_total')?.value,
      Sub_total_iva: this.frm_factura.get('Sub_total_iva')?.value,
      Valor_IVA: this.frm_factura.get('Valor_IVA')?.value,
      Clientes_idClientes: this.frm_factura.get('Clientes_idClientes')?.value
    };

    this.facturaService.insertar(factura).subscribe((respuesta) => {
      if (parseInt(respuesta) > 0) {
        alert('Factura grabada');
        this.navegacion.navigate(['/facturas']);
      }
    });
  }

  cambio(objetoSelect: any) {
    let idCliente = objetoSelect.target.value;
    this.frm_factura.get('Clientes_idClientes')?.setValue(idCliente);
  }

  productosLista(evento) {
    alert('Lista de productos cargando');
    // Aquí deberías implementar la lógica para cargar la lista de productos
  }

  cargaModal(valoresModal: any) {
    const nuevoProducto: any = {
      Descripcion: 'Producto 4',
      Cantidad: 15,
      Precio: 12.23,
      Subtotal: 183.45,  // 15 * 12.23
      IVA: 27.52,        // 15% de 183.45
      Total: 210.97      // 183.45 + 27.52
    };
    this.productoelejido.push(nuevoProducto);
    this.modal.dismissAll();

    this.actualizarTotales();
  }
}