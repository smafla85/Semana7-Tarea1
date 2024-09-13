import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { HttpClient, HttpClientModule } from '@angular/common/http';
import { ClientesService } from '../../Services/clientes.service';
import { FacturaService } from '../../Services/factura.service';
import { ProductoService } from '../../Services/productos.service';
import { ICliente } from '../../Interfaces/icliente';
import { IProducto } from '../../Interfaces/iproducto';

interface ProductoSeleccionado extends IProducto {
  cantidadSeleccionada: number;
  totalProducto: number;
}

@Component({
  selector: 'app-nuevafacturafpdf',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    HttpClientModule
  ],
  templateUrl: './nuevafacturafpdf.component.html',
  styleUrls: ['./nuevafacturafpdf.component.scss']
})
export class NuevaFacturaFPDFComponent implements OnInit {
  facturaForm: FormGroup;
  clientes: ICliente[] = [];
  productos: IProducto[] = [];
  clienteSeleccionado: ICliente | null = null;
  productosSeleccionados: ProductoSeleccionado[] = [];
  subtotal: number = 0;
  iva: number = 0;
  total: number = 0;
  mostrarPreview: boolean = false;
  pdfUrl: string = '';

  constructor(
    private fb: FormBuilder,
    private http: HttpClient,
    private clientesService: ClientesService,
    private facturaService: FacturaService,
    private productoService: ProductoService
  ) {
    this.facturaForm = this.fb.group({
      cliente_id: ['', Validators.required],
      producto_id: ['', Validators.required],
      cantidad: [1, [Validators.required, Validators.min(1)]]
    });
  }

  ngOnInit(): void {
    this.cargarClientes();
    this.cargarProductos();
  }

  cargarClientes() {
    this.clientesService.todos().subscribe({
      next: (data: ICliente[]) => {
        this.clientes = data;
      },
      error: (error) => console.error('Error al cargar clientes:', error)
    });
  }

  cargarProductos() {
    this.productoService.todos().subscribe({
      next: (data: IProducto[]) => {
        this.productos = data;
      },
      error: (error) => console.error('Error al cargar productos:', error)
    });
  }

  onClienteChange() {
    const clienteId = this.facturaForm.get('cliente_id')?.value;
    this.clienteSeleccionado = this.clientes.find(c => c.idClientes === clienteId) || null;
    this.actualizarPreview();
  }

  agregarProducto() {
    if (this.facturaForm.valid) {
      const productoId = this.facturaForm.get('producto_id')?.value;
      const cantidad = this.facturaForm.get('cantidad')?.value;
      const productoSeleccionado = this.productos.find(p => p.idProductos === productoId);
      
      if (productoSeleccionado) {
        const nuevoProducto: ProductoSeleccionado = {
          ...productoSeleccionado,
          cantidadSeleccionada: cantidad,
          totalProducto: cantidad * productoSeleccionado.Valor_Venta
        };
        this.productosSeleccionados.push(nuevoProducto);
        this.facturaForm.patchValue({ producto_id: '', cantidad: 1 });
        this.calcularTotales();
        this.actualizarPreview();
      }
    }
  }

  eliminarProducto(index: number) {
    this.productosSeleccionados.splice(index, 1);
    this.calcularTotales();
    this.actualizarPreview();
  }

  calcularTotales() {
    this.subtotal = this.productosSeleccionados.reduce((acc, producto) => acc + producto.totalProducto, 0);
    this.iva = this.productosSeleccionados.reduce((acc, producto) => {
      return acc + (producto.Graba_IVA ? producto.totalProducto * 0.15 : 0);
    }, 0);
    this.total = this.subtotal + this.iva;
  }

  actualizarPreview() {
    this.mostrarPreview = this.clienteSeleccionado !== null && this.productosSeleccionados.length > 0;
  }

  generarFacturaPDF() {
    if (this.clienteSeleccionado && this.productosSeleccionados.length > 0) {
      const datosFactura = {
        cliente: this.clienteSeleccionado,
        productos: this.productosSeleccionados.map(p => ({
          idProductos: p.idProductos,
          Nombre_Producto: p.Nombre_Producto,
          Cantidad: p.cantidadSeleccionada,
          Valor_Venta: p.Valor_Venta,
          Total: p.totalProducto,
          Graba_IVA: p.Graba_IVA
        })),
        subtotal: this.subtotal,
        iva: this.iva,
        total: this.total
      };

      this.http.post('http://localhost:81/sexto/Proyectos/03MVC/reports/factura.reports.php', datosFactura, { responseType: 'blob' })
        .subscribe({
          next: (data: Blob) => {
            const blob = new Blob([data], { type: 'application/pdf' });
            this.pdfUrl = window.URL.createObjectURL(blob);
            window.open(this.pdfUrl, '_blank');
          },
          error: (error) => console.error('Error al generar la factura PDF:', error)
        });
    }
  }
}