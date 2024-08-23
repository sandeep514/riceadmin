<?php

namespace App\Export;

use App\LivePrice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
// use PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;

use DB;
use Carbon\Carbon;


class LivePricesExport implements FromCollection, WithHeadings, WithStyles
{
    public $boldTextIndex = [];
    public function headings(): array
    {
        return [
            "Rice", "State", "Crop Year", "Min Price", "Max Price","Last Seven Day Avg%","Last Month Day Avg%"
        ];
    }


    // public function registerEvents(): array{
    //     $cellRange      = 'A1:B1';
    //     return [
    //         AfterSheet::class    => function(AfterSheet $event) use($cellRange) {
    //             $event->sheet->getStyle($cellRange)->applyFromArray([
    //                 'borders' => [
    //                     'allBorders' => [
    //                         'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
    //                         'color' => ['argb' => '000000'],
    //                     ],
    //                 ],
    //             ])->getAlignment()->setWrapText(true);
    //         },
    //     ];
    // }

    public function collection()
    {

        $processedData = [];
        $lastRecord = LivePrice::query()
            ->where('name', '!=', '0')
            ->where('form', '!=', '0')
            ->whereNotNull('min_price')
            ->whereNotNull('max_price')
            ->latest('id')
            ->first();

        if ($lastRecord != null) {
            $lastToLastDate = LivePrice::query()
                ->where('name', '!=', '0')
                ->where('form', '!=', '0')
                ->whereNotNull('min_price')
                ->whereNotNull('max_price')
                ->whereDate('created_at', '<', $lastRecord->created_at->format('Y-m-d'))
                ->latest()
                ->first();

            if ($lastToLastDate) {
                $date = $lastToLastDate->created_at->format("Y-m-d");

                $data = LivePrice::query()
                    ->has('name_rel')
                    ->whereHas('form_rel')
                    ->with(['name_rel', 'form_rel'])
                    ->whereNotNull('min_price')
                    ->whereNotNull('max_price')
                    ->select(['name', 'form', 'state', 'min_price', 'max_price', 'created_at', 'cropYear','name_order','form_order'])
                    ->selectRaw('(max_price + min_price) / 2 as avg_price')->orderBy('name_order')->orderBy('state_order')
                    ->selectSub(function ($query) {
                        $query->from('live_prices as lw')
                            ->selectRaw('round((avg_price - ((min_price + max_price) / 2)) / ((min_price + max_price) / 2) * 100, 2)')
                            ->whereColumn('lw.name', 'live_prices.name')
                            ->whereColumn('lw.state', 'live_prices.state')
                            ->whereColumn('lw.form', 'live_prices.form')
                            ->whereRaw('DATEDIFF(live_prices.created_at,lw.created_at)=7')
                            ->limit(1);
                    }, 'last_week_price_change')
                    ->selectSub(function ($query) {
                        $query->from('live_prices as lm')
                            ->selectRaw('round((avg_price - ((min_price + max_price) / 2)) / ((min_price + max_price) / 2) * 100, 2)')
                            ->whereColumn('lm.name', 'live_prices.name')
                            ->whereColumn('lm.state', 'live_prices.state')
                            ->whereColumn('lm.form', 'live_prices.form')
                            ->whereRaw('DATEDIFF(live_prices.created_at,lm.created_at)=30')
                            ->limit(1);
                    }, 'last_month_price_change')
                    ->whereIn(
                        DB::raw('date(created_at)'),
                        [$lastRecord->created_at->format('Y-m-d')]
                        // [$lastRecord->created_at->format('Y-m-d'), $lastToLastDate->created_at->format('Y-m-d')]
                    )->where('status' , 1)
                    ->get();


                $latstRecord = $lastRecord->created_at->format('Y-m-d');
                $sorted = [];
                $processedArray = [];
                $loacalFunctionboldTextIndex = [];

                foreach($data as $k => $v){
                    $sorted[$v->name_rel['name']][] = [
                                "form"                  => $v->form_rel['form_name'],
                                "state"                 => $v->state,
                                "cropYear"              => $v->cropYear,
                                "min_price"             => '₹' . number_format(($v->min_price)),
                                "max_price"             => '₹' . number_format(($v->max_price)),
                                'last_week_percent'     => $v['last_week_price_change'].'%',
                                'last_month_percent'    => (($v['last_month_price_change'] == null)? 0 : $v['last_month_price_change']).'%'
                            ];
                }

                foreach($sorted as $k => $v){
                    $processedArray[] = ['name' => $k];
                    foreach($v as $key => $val){
                        $processedArray[] = $val;
                    }
                    $processedArray[] = ['name' => ''];
                }
                foreach ($processedArray as $k => $v) {
                    if (is_array($v) && isset($v['name'])) {
                        if ($v['name'] != '') {
                            $loacalFunctionboldTextIndex[] = ($k + 1);
                        }
                    }
                }

                $this->boldTextIndex = $loacalFunctionboldTextIndex;
                // dd($loacalFunctionboldTextIndex);
                // dd(collect($processedArray));
                return collect($processedArray);
                dd($sorted);    

                foreach ($data->sortBy('name_rel.order') as $k => $v) {
                    $replaceHignfn = explode('-', $v->name_rel->type);
                    $implodeUnderscore = implode('_', $replaceHignfn);
                    $processedData[$implodeUnderscore][$v->name_rel->name][$v->form_rel->form_name][$v->created_at->format('Y-m-d')] = $v;
                }


                $fiilteredProcessedData = [];
                foreach ($data->sortBy('form_rel.order') as $k => $v) {
                    $replaceHignfn = explode('-', $v->name_rel->type);
                    $implodeUnderscore = implode('_', $replaceHignfn);
                    $fiilteredProcessedData[$v->name_rel->name][$v->form_rel->form_name][$v->created_at->format('Y-m-d')] = $v;
                }


                foreach ($processedData as $k => $v) {
                    foreach ($v as $kk => $vv) {
                        $processedData[$k][$kk] = $fiilteredProcessedData[$kk];
                    }
                }

                

                foreach ($processedData as $k => $v) {
                    if (is_array($v)) {
                        foreach ($v as $key => $value) {
                            if (is_array($value)) {
                                foreach ($value as $ke => $val) {
                                    if (!array_key_exists($latstRecord, $val)) {
                                        unset($processedData[$k][$key][$ke]);
                                    }
                                }
                            }
                        }
                    }
                }


                foreach ($processedData as $k => $v) {
                    if (is_array($v)) {
                        foreach ($v as $key => $val) {
                            if (empty($val)) {
                                unset($processedData[$k][$key]);
                            } else {
                                foreach ($val as $kk => $vv) {
                                    if ($kk != 0) {
                                        $processedData[$k][$key][$kk]['isHide'] = 'true';
                                    }
                                }
                            }
                        }
                    }
                }

                $newData = collect($processedData)->map(function ($item) {
                    return collect($item)->map(function ($innerItem) use ($item) {
                        $onlyValues = array_values($innerItem);
                        $onlyKeys = array_keys($innerItem);
                        foreach ($onlyValues as $k => $v) {
                            $onlyValues[$k]['is_hide'] = ($k == 0) ? 'false' : 'true';
                        }


                        $data = array_combine($onlyKeys, $onlyValues);
                        return $data;
                    });
                })->toArray();
                $order = [];
                foreach ($newData as $k => $v) {
                    foreach ($v as $kk => $vv) {
                        $order[$k][] = [$kk => $vv];
                    }
                }

                $myNewData = [];
                foreach ($order as $k => $v) {
                    foreach ($v as $kk => $vv) {
                        $newDataProcess = [];
                        foreach ($vv as $key => $value) {
                            foreach ($value as $ke => $val) {
                                $newDataProcess[] = [$ke => $val];
                            }
                            $myNewData[$k][$kk][$key] = $newDataProcess;
                        }
                    }
                }
                
                foreach ($myNewData as $k => $v) {
                    foreach ($v as $kk => $vv) {
                        foreach ($vv as $key => $val) {
                            foreach ($val as $ke => $value) {
                                foreach ($value as $kee => $values) {
                                    $sorted[] = [
                                        "name"                  => $values[$lastRecord->created_at->format('Y-m-d')]->name_rel['name'],
                                        "form"                  => $values[$lastRecord->created_at->format('Y-m-d')]->form_rel['form_name'],
                                        "state"                 => $values[$lastRecord->created_at->format('Y-m-d')]->state,
                                        "cropYear"              => $values[$lastRecord->created_at->format('Y-m-d')]->cropYear,
                                        "min_price"             => '₹' . number_format(($values[$lastRecord->created_at->format('Y-m-d')]->min_price)),
                                        "max_price"             => '₹' . number_format(($values[$lastRecord->created_at->format('Y-m-d')]->max_price)),
                                        'last_week_percent'     => $values[$lastRecord->created_at->format('Y-m-d')]['last_week_price_change'].'%',
                                        'last_month_percent'    => (($values[$lastRecord->created_at->format('Y-m-d')]['last_month_price_change'] == null)? 0 : $values[$lastRecord->created_at->format('Y-m-d')]['last_month_price_change']).'%'
                                    ];
                                }
                            }
                        }
                    }
                }

                $newFormat = [];
                foreach (collect($sorted)->groupBy('name') as $key => $values) {
                    $newFormat[] = ['name' => $key];
                    foreach ($values as $kl => $vl) {
                        unset($vl['name']);
                        $newFormat[] = $vl;
                    }
                    $newFormat[] = ['name' => ''];
                }
                foreach ($newFormat as $k => $v) {
                    if (is_array($v) && isset($v['name'])) {
                        if ($v['name'] != '') {
                            $loacalFunctionboldTextIndex[] = ($k + 1);
                        }
                    }
                }

                $this->boldTextIndex = $loacalFunctionboldTextIndex;
                dd(collect($newFormat));
                return collect($newFormat);
            }
        }
        return collect();
    }

    public function styles(Worksheet $sheet)
    {
        $style = [
            1    => ['font' => ['bold' => true]],
        ];

        foreach ($this->boldTextIndex as $k => $v) {
            $style['A' . ($v + 1)] = ['font' => ['bold' => true]];
            $style['A' . ($v + 1) . ':G' . ($v + 1)] = ['borders' => ['bottom' => ['borderStyle' => Border::BORDER_THICK]]];
            $sheet->getStyle('A' . ($v + 1))->getAlignment()->setHorizontal('left');

        }
        return $style;
    }
}
