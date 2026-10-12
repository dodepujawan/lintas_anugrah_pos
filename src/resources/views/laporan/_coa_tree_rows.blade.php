@php
$displayAmount = function ($value) {
if ($value < 0) {
    $num=number_format(abs($value), 2, ',' , '.' );
    return '<span class="currency">(</span>&nbsp;<span class="num">' . $num . '</span>' ;
    }

    $num=number_format($value, 2, ',' , '.' );
    return '<span class="num">' . $num . '</span>' ;
    };
    @endphp

    @foreach($nodes as $node)
    @php
    $indentClass=$level> 0 ? 'ps-' . min(5, $level + 2) : '';
    $indentStyle = $level > 0 ? 'padding-left: ' . ($level * 16) . 'px;' : '';
    $rowClass = $level === 0 ? 'table-light' : '';
    $amountClass = $node['coa']->tipe === 'Aset'
    ? 'text-primary'
    : ($node['coa']->tipe === 'Kewajiban' ? 'text-danger' : 'text-success');
    $displayLabel = $node['coa']->nama_akun;
    @endphp
    <tr class="tree-row account-row {{ $rowClass }}" data-parent-id="{{ $parentId }}">
        @if($show_code ?? true)
        <td class="px-3 code-col text-start">{{ $node['coa']->kode_akun }}</td>
        @endif
        <td class="px-3 {{ $indentClass }}" style="{{ $indentStyle }}">
            @if($mobile_code ?? false)
            <span class="mobile-code">{{ $node['coa']->kode_akun }}</span>
            @endif
            {{ $displayLabel }}
        </td>
        <td class="px-3 fw-bold amount {{ $amountClass }}">{!! $displayAmount($node['subtotal']) !!}</td>
    </tr>
    @if(!empty($node['children']))
    @include('laporan._coa_tree_rows', ['nodes' => $node['children'], 'level' => $level + 1, 'parentId' => $node['coa']->id, 'show_code' => $show_code ?? true, 'mobile_code' => $mobile_code ?? false])
    @endif
    @endforeach
