<?php

namespace App\DataTables;

use App\Quality;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class QualitiesDataTable extends DataTable
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
            ->editColumn('name', function ($model) {
                return ucwords(str_replace('-', ' ', $model->type))
                    . ' - ' . $model->name . ' ' . $model->form_name . ' (' . $model->type_name . ')';
            })
            ->editColumn('created_at', function ($model) {
                return $model->created_at->diffForHumans();
            })
            ->addColumn('action', function ($model) {
                return view('qualities._actions', ['model' => $model]);
            });
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Quality $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Quality $model)
    {
        //return $model->newQuery()->with(['nameRel','typeRel','formRel']);
        return $model->newQuery()->select([
            'qualities.id',
            'rice_names.type as type',
            'rice_names.name as name',
            'rice_forms.form_name as form_name',
            'rice_types.name as type_name',
            'qualities.created_at'
        ])->leftJoin('rice_names', 'qualities.name', '=', 'rice_names.id')
            ->leftJoin('rice_forms', 'qualities.type', '=', 'rice_forms.id')
            ->leftJoin('rice_types', 'qualities.category', '=', 'rice_types.id');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('qualities-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('Bfrtip')
            ->orderBy(0, 'desc')
            ->buttons(
                Button::make('create'),
                Button::make('export'),
                Button::make([
                    'text' => '<i class="fa fa-upload" aria-hidden="true"></i> &nbsp; Import'
                ])->action('window.location="' . route('import.quality') . '"'),
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
            Column::make('id')->searchable(false),
            'name' => ['name' => 'rice_names.name'],
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
        return 'Qualities_' . date('YmdHis');
    }
}
