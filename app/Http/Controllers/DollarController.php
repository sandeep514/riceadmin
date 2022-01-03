<?php
namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;

class DollarController extends Controller
{
    public function index()
    {
        return View('defaultMaster.create');
    }
    public function save(Request $request)
    {
        $content = file_get_contents(asset('uploads/excel/bag cost.xlsx')); 
        $lines = array_map("rtrim", explode("\n", $content));
        dd($line);
        
        $file = $request->file('file');
        if ($file) {
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension(); 

            $tempPath = $file->getRealPath();
            $fileSize = $file->getSize(); 
            
            $location = 'uploads'; 

            $file->move($location, $filename);

            $filepath = public_path($location . "/" . $filename);

            $file = fopen($filepath, "r");
            $importData_arr = array(); 

            $i = 0;

            while (($filedata = fgetcsv($file, 1000, ",")) !== FALSE) {
                $num = count($filedata);
                if ($i == 0) {
                    $i++;
                    continue;
                }
                for ($c = 0; $c < $num; $c++) {
                    $importData_arr[$i][] = $filedata[$c];
                }
                $i++;
            }
            fclose($file); 

            $j = 0;
            foreach ($importData_arr as $importData) {
                dd($importData);
                $name = $importData[1]; 
                $email = $importData[3]; 

                $j++;

                try {
                    DB::beginTransaction();
                    Player::create([
                    'name' => $importData[1],
                    'club' => $importData[2],
                    'email' => $importData[3],
                    'position' => $importData[4],
                    'age' => $importData[5],
                    'salary' => $importData[6]
                    ]);
                
                    $this->sendEmail($email, $name);
                    DB::commit();
                } catch (\Exception $e) {
                    //throw $th;
                    DB::rollBack();
                }
            }

            return response()->json([
                'message' => "$j records successfully uploaded"
            ]);
        } else {
            throw new \Exception('No file was uploaded', Response::HTTP_BAD_REQUEST);
        }
    }

    public function update()
    {

    }

}