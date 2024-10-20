<table>
    <tr>
        <td>VENTAS REALIZADAS</td>
    </tr>

    <thead>
        <tr>
            <th style="background: #50ccf1">#</th>
            <th width="35" style="background: #50ccf1">CLIENTE</th>
            <th width="35" style="background: #50ccf1">Nº DE TRANSACCIÓN</th>
            <th width="30" style="background: #50ccf1">MÉTODO DE PAGO</th>
            <th width="20" style="background: #50ccf1">TOTAL</th>
            <th width="25" style="background: #50ccf1">FECHA DE COMPRA</th>
            <th width="35" style="background: #50ccf1">DIRECCIÓN</th>
            <th width="20" style="background: #50ccf1">ESTADO</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($sales as $key => $sale)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $sale->user->name . ' ' . $sale->user->last_name }}</td>
                <td>{{ $sale->n_transaction }}</td>
                <td>{{ $sale->method_payment }}</td>
                <td>${{ $sale->total }}</td>
                <td>{{ $sale->created_at }}</td>
                <td>
                    @if (isset($sale->sale_address_list) && count($sale->sale_address_list) > 0 && isset($sale->sale_address_list[0]->id))
                        @if (isset($sale->sale_address_list[0]->province))
                            {{ $sale->sale_address_list[0]->province->name }}
                        @endif
                        /
                        @if (isset($sale->sale_address_list[0]->city))
                            {{ $sale->sale_address_list[0]->city->name }}
                        @endif
                        /
                        @if (isset($sale->sale_address_list[0]->parish))
                            {{ $sale->sale_address_list[0]->parish->name }}
                        @endif
                    @else
                        <!-- Si no se cumplen las condiciones, puedes mostrar algo alternativo o dejarlo vacío -->
                        No registra dirección
                    @endif
                </td>
                <td>
                    @if ($sale->state == 1)
                        <span class="badge badge-warning">Pendiente</span>
                    @elseif($sale->state == 2)
                        <span class="badge badge-success">Aprovado</span>
                    @elseif($sale->state == 3)
                        <span class="badge badge-danger">Rechazado</span>
                    @elseif($sale->state == 4)
                        <span class="badge badge-info">Cancelado</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
