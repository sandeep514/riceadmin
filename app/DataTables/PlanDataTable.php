<?php

namespace App\DataTables;

use App\Plan;
use App\SubPlan;
use App\ChartInterval;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class PlanDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()->eloquent($query)->editColumn('created_at', function ($model) {
            return $model
                ->created_at
                ->diffForHumans();
        })->editColumn('sub_plan', function ($model) {

            $html = '<ul class="row" style="padding: 0; text-align: center;list-style: none">';

            $data[$model->plan_name]['plan'] = $model;
            $sub_plan[] = json_decode($model->sub_plan, true);
            $chart_int = json_decode($model->chart_int, true);
            $SubPlan = [];
            foreach ($sub_plan as $key => $value) {
                foreach ($value as $ke => $val) {
                    $SubPlan[$ke]['data'] = SubPlan::where(['id' => $ke])->first();
                    $SubPlan[$ke]['price'] = $value;
                }
            }
            $data[$model->plan_name]['SubPlan'] = $SubPlan;
            $data[$model->plan_name]['ChartInt'] = ChartInterval::select('id', 'name')
                ->whereIn('id', array_values($chart_int))->get();
            foreach ($data as $k => $v) {
                foreach ($v['SubPlan'] as $kee => $vaa) {
                    $html .= '<li class="col-md-12" style="text-align: left">' . $vaa['data']->name . ' - &#x20B9;' . $vaa['price'][$vaa['data']->id]['offerPrice'] . '</li>';
                }
            };
            $html .= '</ul>';
            return $html;
        })->editColumn('chart_int', function ($model) {
            $html = '<ul class="row">';
            $data[$model->plan_name]['plan'] = $model;
            $chart_int = json_decode($model->chart_int, true);
            $chertInterval = ChartInterval::select('id', 'name')->whereIn('id', array_values($chart_int))->get();
            foreach ($chertInterval as $k => $v) {
                $html .= '<li class="col-md-4">' . $v->name . '</li>';
            }
            $html .= '</ul>';
            return $html;
        })->addColumn('action', function ($model) {
            return view('plans._actions', ['model' => $model]);
        })->rawColumns(['action', 'sub_plan', 'chart_int']);
    }
    public function query(Plan $model)
    {
        return $model->newQuery();
    }
    public function html()
    {
        return $this->builder()
            ->setTableId('packings-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('Bfrtip')
            ->orderBy(0, 'desc')
            ->buttons(Button::make('create'), Button::make('export'), Button::make('print'), Button::make('reset'), Button::make('reload'));
    }
    protected function getColumns()
    {
        return [Column::make('id'), Column::make('plan_name')
            ->title('Plan Name'), Column::make('sub_plan')
            ->title('sub_plan'), Column::make('chart_int')
            ->title('chart_int')
            ->class('chart_int'), Column::make('created_at'), Column::computed('action')
            ->exportable(false)
            ->printable(false)
            ->width(150)
            ->addClass('text-center'),];
    }
    protected function filename(): string
    {
        return 'Packings_' . date('YmdHis');
    }
}
