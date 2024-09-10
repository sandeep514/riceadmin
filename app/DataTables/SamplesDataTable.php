<?php

namespace App\DataTables;

use App\Quality;
use App\Sample;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class SamplesDataTable extends DataTable
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
//            ->editColumn('photo', function($model){
//                return '<img src="'.asset('sample-images/'.$model->photo).'" width="100" />';
//            })
            ->editColumn('supplier', function($model){
                return $model->supplier_rel->name;
            })
            ->editColumn('quality', function($model){
                return '<a href="javascript:void(0)"
                            data-nameType="'.ucwords(str_replace('-',' ',$model->quality_rel->nameRel->type)).'"
                            data-name="'.$model->quality_rel->nameRel->name.'"
                            data-formName="'.$model->quality_rel->formRel->form_name.'"
                            data-type="'.$model->quality_rel->typeRel->name.'"
                            class="view_quality_details">View Details</a>';
            })
            ->editColumn('packing', function($model){
                return $model->packing_rel->code;
            })
            ->editColumn('packing_type', function($model){
                return $model->packing_type_rel->name;
            })
            ->editColumn('created_at', function($model){
                return $model->created_at->diffForHumans();
            })
            ->addColumn('action', function($model){
                return view('samples._actions',['model'=>$model]);
            })->rawColumns(['action','photo','quality']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Smaple $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Sample $model)
    {
        return $model->with(['supplier_rel','packing_rel','quality_rel','packing_type_rel'])->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $columns = $this->getColumns();
        unset($columns[0]);
        return $this->builder()
                    ->setTableId('smaples-table')
                    ->columns($columns)
                    ->minifiedAjax()
                    ->dom('Bfrtip')
                    ->orderBy(0,'desc')
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
            Column::make('supplier'),
            Column::make('quality'),
            Column::make('packing'),
            Column::make('qty'),
//            Column::make('photo'),
            Column::make('created_at'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(150)
                ->addClass('text-center')
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'Smaples_' . date('YmdHis');
    }
}
