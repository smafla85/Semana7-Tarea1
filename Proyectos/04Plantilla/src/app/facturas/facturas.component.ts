import { Component, OnInit } from '@angular/core';
import { SharedModule } from 'src/app/theme/shared/shared.module';
import { IFactura } from '../Interfaces/factura';
import { Router, RouterLink } from '@angular/router';
import { FacturaService } from '../Services/factura.service';

@Component({
  selector: 'app-facturas',
  standalone: true,
  imports: [SharedModule, RouterLink],
  templateUrl: './facturas.component.html',
  styleUrl: './facturas.component.scss'
})
export class FacturasComponent implements OnInit {
  listafacturas: IFactura[] = [];

  constructor(
    private facturaServicio: FacturaService,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.cargarFacturas();
  }

  cargarFacturas() {
    this.facturaServicio.todos().subscribe((data: IFactura[]) => {
      this.listafacturas = data;
    });
  }

  eliminar(idFactura: number) {
    if (confirm('¿Está seguro de que desea eliminar esta factura?')) {
      this.facturaServicio.eliminar(idFactura).subscribe({
        next: () => {
          console.log('Factura eliminada exitosamente');
          this.cargarFacturas(); // Recargar la lista después de eliminar
        },
        error: (error) => {
          console.error('Error al eliminar la factura:', error);
          // Aquí puedes manejar el error, por ejemplo, mostrando un mensaje al usuario
        }
      });
    }
  }
}