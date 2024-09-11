<?php

namespace App\DataTables;

use App\SampleRegister;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class SampleRegistersDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->editColumn('sntc_no', function ($model) {
                return str_pad($model->sntc_no, 5, 0, STR_PAD_LEFT);
            })
            ->editColumn('supplier', function ($model) {
                return $model->supplier_rel->name;
            })
            ->editColumn('quality', function ($model) {
                return '<a href="javascript:void(0)"
                            data-nameType="' . ucwords(str_replace('-', ' ', $model->quality_rel->nameRel->type)) . '"
                            data-name="' . $model->quality_rel->nameRel->name . '"
                            data-formName="' . $model->quality_rel->formRel->form_name . '"
                            data-type="' . $model->quality_rel->typeRel->name . '"
                            class="view_quality_details">View Details</a>';
            })
            ->editColumn('created_at', function ($model) {
                return $model->created_at->diffForHumans();
            })
            ->addColumn('action', function ($model) {
                return view('sample-registers._actions', ['model' => $model]);
            })->rawColumns(['action', 'quality']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\SampleRegister $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(SampleRegister $model)
    {
        return $model->with(['supplier_rel', 'quality_rel'])->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('sampleregisters-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('Bfrtip')
            ->orderBy(1, 'desc')
            ->buttons(
                Button::make('create'),
                Button::make('export'),
                Button::make('print'),
                Button::make('reset'),
                Button::make('reload')
            );
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            Column::make('id'),
            Column::make('date'),
            Column::make('sntc_no'),
            Column::make('supplier'),
            Column::make('quality'),
            Column::make('seller_qty'),
            Column::make('seller_offer'),
            Column::make('created_at'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(150)
                ->addClass('text-center'),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'SampleRegisters_' . date('YmdHis');
    }
}
