<?php
    
    namespace App;
    
    use Illuminate\Database\Eloquent\Model;
    
    class Order extends Model
    {
        protected $table = "orders";
        protected $fillables = [
            'user_id',
            'transaction_id',
            'plan_id',
            'plan_name',
            'start_date',
            'end_date',
            'sub_plan_name',
            'payment_type',
            'amount',
            'sub_plan_price',
            'status'
        ];
        
        public function sub_plan()
        {
            return $this->belongsTo('App\Plan', 'plan_id', 'id');
        }

//        public function ChartInterval()
//        {
//            return $this->belongsTo('App\ChartInterval', 'chart_int', 'id');
//        }
    }
