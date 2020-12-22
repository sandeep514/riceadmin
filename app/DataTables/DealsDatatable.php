<?php

namespace App\DataTables;

use App\Deal;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class DealsDatatable extends DataTable
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
            ->editColumn('is_direct_deal', function($model){
                return ($model->is_direct_deal == 1)?'Yes':'No';
            })
            ->editColumn('sntc_no', function($model){
                if($model->sntc_no == 0){
                    return '<i>Direct Deal</i>';
                }else{
                    return str_pad($model->sntc_no, 5,0,STR_PAD_LEFT);
                }
            })
            ->editColumn('seller', function($model){
                return $model->seller_rel->name;
            })
            ->editColumn('buyer', function($model){
                return $model->buyer_rel->name;
            })
            ->addColumn('action', function($model){
                return view('deals._actions',['model'=>$model]);
            })
            ->rawColumns(['action','quality','sntc_no']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Deal $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Deal $model)
    {
        return $model->newQuery()->with(['seller_rel','buyer_rel']);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('dealsdatatable-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->dom('Bfrtip')
                    ->orderBy(1)
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
            Column::make('is_direct_deal'),
            Column::make('contract_no'),
            Column::make('sntc_no'),
            Column::make('buyer'),
            Column::make('seller'),
            Column::make('quantity'),
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
        return 'Deals_' . date('YmdHis');
    }
}
