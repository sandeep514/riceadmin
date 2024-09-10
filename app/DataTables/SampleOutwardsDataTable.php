<?php

namespace App\DataTables;

use App\SampleOutward;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class SampleOutwardsDataTable extends DataTable
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
            ->editColumn('buyer', function($model){
                return $model->buyer_rel->name;
            })->editColumn('sntc_no', function($model){
                return str_pad($model->sntc_no, 5,0,STR_PAD_LEFT);
            })
            ->editColumn('quality', function($model){
                return '<a href="javascript:void(0)"
                            data-nameType="'.ucwords(str_replace('-',' ',$model->quality_rel->nameRel->type)).'"
                            data-name="'.$model->quality_rel->nameRel->name.'"
                            data-formName="'.$model->quality_rel->formRel->form_name.'"
                            data-type="'.$model->quality_rel->typeRel->name.'"
                            class="view_quality_details">View Details</a>';
            })->editColumn('bag_type', function($model){
                return $model->packingType_rel->name;
            })->editColumn('created_at', function($model){
                return $model->created_at->diffForHumans();
            })
            ->addColumn('action', function($model){
                return view('sample-outwards._actions',['model'=>$model]);
            })->rawColumns(['action','quality']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\SampleOutward $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(SampleOutward $model)
    {
        return $model->with(['buyer_rel','quality_rel','packingType_rel'])->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('sampleoutwards-table')
                    ->columns($this->getColumns())
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
            Column::make('sntc_no'),
            Column::make('buyer'),
            Column::make('quality'),
            Column::make('bag_type'),
            Column::make('awb_no'),
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
        return 'SampleOutwards_' . date('YmdHis');
    }
}
