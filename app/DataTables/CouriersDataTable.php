<?php

namespace App\DataTables;

use App\Courier;
use App\Sample;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class CouriersDataTable extends DataTable
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
            ->editColumn('created_at', function($model){
                return $model->created_at->diffForHumans();
            })
            ->editColumn('samples', function($model){
                $samples = Sample::with(['quality_rel','supplier_rel','packing_rel','packing_type_rel'])
                    ->whereIn('id',array_keys(json_decode($model->samples,true)))->get();
                return view('couriers.samples-modal',['samples'=>$samples,'model'=>$model]);
            })
            ->editColumn('sent_via',function($model){
                return Courier::$sentVia[$model->sent_via];
            })
            ->addColumn('action', function($model){
                return view('couriers._actions',['model'=>$model]);
            })->rawColumns(['action','samples']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Courier $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Courier $model)
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $hasPermissionForCreate = \App\Permission::hasPermission('create');
        $buttons = [
            Button::make('export'),
            Button::make('print'),
            Button::make('reset'),
            Button::make('reload')
        ];
        if($hasPermissionForCreate){
            array_unshift($buttons,Button::make('create'));
        }
        return $this->builder()
                    ->setTableId('couriers-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->dom('Bfrtip')
                    ->orderBy(1)
                    ->buttons($buttons);
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
            Column::make('samples'),
            Column::make('sent_via'),
            Column::make('details'),
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
    protected function filename()
    {
        return 'Couriers_' . date('YmdHis');
    }
}
