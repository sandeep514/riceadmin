<?php

namespace App\DataTables;

use App\SampleLabReport;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class SampleLabReportDatatable extends DataTable
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
            ->editColumn('created_at', function ($model) {
                return $model->created_at->diffForHumans();
            })
            ->addColumn('action', function ($model) {
                return view('sample-lab-report._actions', ['model' => $model]);
            });
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\SampleLabReportDatatable $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(SampleLabReport $model)
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
        return $this->builder()
            ->setTableId('samplelabreportdatatable-table')
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
            Column::make('sntc_no'),
            Column::make('length'),
            Column::make('ad_mixture'),
            Column::make('moisture'),
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
    protected function filename(): string
    {
        return 'SampleLabReport_' . date('YmdHis');
    }
}
