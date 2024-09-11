<?php

namespace App\DataTables;

use App\User;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use Carbon\Carbon;


class AllUsersDatatable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $datatable = datatables()
            ->eloquent($query)
            ->editColumn('created_at', function ($model) {
                $carbonDate = Carbon::create($model->created_at);

                return ($carbonDate->format('d-m-Y'));
                // return $model->created_at->diffForHumans();
            })
            ->editColumn('role', function ($model) {
                return $model->role_rel->role_name;
            });

        return $datatable;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\User $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(User $model)
    {
        if (request()->has('from')) {
            return $model->whereDate('created_at', '>=', request()->from)->whereDate('created_at', '<=', request()->to)->with(['bagVendor', 'role_rel', 'field_runner_rel.designation_rel'])->newQuery();
        } else {
            return $model->with(['bagVendor', 'role_rel', 'field_runner_rel.designation_rel'])->newQuery();
        }
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('users-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('Bfrtip')
            ->orderBy(0, 'desc')
            ->buttons(
                // Button::make('create'),
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
        $columnsArray = [
            Column::make('id'),
            Column::make('name'),
        ];
        if (request()->role == 8) {
            $columnsArray[] = Column::make('bagCategory');
        }
        if (request()->role != 3) {
            $columnsArray[] = Column::make('email');
        } elseif (request()->role == 3) {
            $columnsArray[] = Column::make('designation');
        }
        $columnsArray[] = Column::make('role');
        $columnsArray[] = Column::make('mobile');
        $columnsArray[] = Column::make('created_at')
            // $columnsArray[] =  Column::computed('action')
            ->exportable(false)
            ->printable(false)
            ->width(150)
            ->addClass('text-center');
        return $columnsArray;
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'Users_' . date('YmdHis');
    }
}
