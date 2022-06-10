<?php

namespace App\Http\Controllers;

use App\Mail\ForgotPasswordMail;
use App\Models\Agent;
use App\Models\AgentBillplan;
use App\Models\AgentComission;
use App\Models\BillPlan;
use App\Models\User;
use App\Providers\EncreptDecrept;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use DB;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\URL;
//use Spatie\ArrayToXml\ArrayToXml;
use Tymon\JWTAuth\Facades\JWTAuth;
// use App\Services\EncreptDecrept;

class AgentController extends Controller
{    
    
    // agentlist page
    public function agentlist(Request $request)
    {
        $perpage = 2;
        $agent = Agent::orderBy('id', 'DESC');
        $activeagents = Agent::where('status', 'ACTIVE');
        $suspendedagents = Agent::where('suspended', 'YES'); 
        
        if($request->ajax()){
            if($request->search){
                $search_key = $request->search;
                $agent->where(function($agent1) use ($search_key){
                    $agent1 = $agent1->where('firstname', 'LIKE', "%{$search_key}%")->orWhere('lastname', 'LIKE', "%{$search_key}%")->orWhere('email', 'LIKE', "%{$search_key}%")->orWhere('company_name', 'LIKE', "%{$search_key}%");
                });
                $activeagents->where(function($activeagents1) use ($search_key){
                    $activeagents1 = $activeagents1->where('firstname', 'LIKE', "%{$search_key}%")->orWhere('lastname', 'LIKE', "%{$search_key}%")->orWhere('email', 'LIKE', "%{$search_key}%")->orWhere('company_name', 'LIKE', "%{$search_key}%");
                });
                $suspendedagents->where(function($suspendedagents1) use ($search_key){
                    $suspendedagents1 = $suspendedagents1->where('firstname', 'LIKE', "%{$search_key}%")->orWhere('lastname', 'LIKE', "%{$search_key}%")->orWhere('email', 'LIKE', "%{$search_key}%")->orWhere('company_name', 'LIKE', "%{$search_key}%");
                });
                
            }
           

        }
        

        $agent = $agent->paginate($perpage);

        if($request->ajax()){
            $response_ajex['totalrecords'] = $agent->total();
            $response_part['records'] = $agent;
            $response_part['page'] = 'agentlist';
            $view = view('user.data',$response_part)->render();
            $response_ajex['html'] = $view;
            $response_ajex['activeagents'] = $activeagents->count();
            $response_ajex['suspendedagents'] = $suspendedagents->count(); 
            return response()->json($response_ajex);
        }

        $response['records'] = $agent;
        $response['totalrecords'] = $agent->total();      
        $response['activeagents'] = $activeagents->count();
        $response['suspendedagents'] = $suspendedagents->count(); 

        return view('user.agentlist',$response);
    }

    // public function agentlistajax()
    // {
    //     $agent = Agent::orderBy('id', 'DESC')->get();           
    //         foreach($agent as $row){ 
    //             $encrypted_id = EncreptDecrept::encrept($row->id); 
    //             $go_to = '<a href="'.url('').'/agentcomission/'.$encrypted_id.'/"><i class="fas fa-info-circle"></i></a>';  
                
               
                
    //             $comission = '<a title="Agent Commission" class="fa fa-money" href="'.url('').'/agentcomission/'.$encrypted_id.'"><i class="fas fa-money-bill-alt text-success"></i></a>';
    //             $tenent = '<a href="'.url('').'/tenent/admin/'.$encrypted_id.'"><i class="fas fa-users"></i></a>';
    //             //$action = '<b><a href="'.url('').'/agent/edit/'.$row->id.'" class="action-icon"><i class="mdi mdi-square-edit-outline"></i></a></b><a href="/agent/password/reset/'.$row->id.'"><i class="mdi mdi-link-variant"></i></a>';

    //             // modify for editable datatabel data
    //             $action = '<b><a  class="action-icon"><i class="mdi mdi-square-edit-outline"></i></a></b><a ><i class="mdi mdi-link-variant"></i></a>';
    //             $status = '<span class="badge bg-soft-danger text-danger status" id="status'.$row->id.'">'.$row->status.'</span>';
    //             if($row->status == 'ACTIVE') $status = '<span class="badge bg-soft-success text-success status" id="status'.$row->id.'">'.$row->status.'</span>';
                
    //             $suspended = '<a><span class="badge bg-soft-danger text-danger suspended" id="suspended'.$row->id.'">'.$row->suspended.'</span></a>';
    //             if($row->suspended == 'NO') $suspended = '<a><span class="badge bg-soft-success text-success suspended" id="suspended'.$row->id.'">'.$row->suspended.'</span></a>';
                


                        
    //         $records[] = [
    //             'go_to' => $go_to,
    //             'agent_code' => $row->id,
    //             'fname' => $row->firstname,
    //             'lname' => $row->lastname,
    //             //'name' => $row->firstname.' '.$row->lastname,
    //             'company_name' => $row->company_name,
    //             'email' => $row->email,
    //             'balance' => $row->balance,  
    //             'status' => $status,
    //             'suspended' => $suspended,
    //             'comission' => $comission,
    //             'tenent' => $tenent,  
    //             'action' => $action,
    //             'id' => $encrypted_id     
    //         ];
    //     }

    //     $response['data'] = $records;    
    //     return response()->json($response);      
   
    // }

    // agentlist page
    public function agentlist_update_ajex(Request $request){
        //return response()->json($request->all());
        $id = EncreptDecrept::decrept($request->id);
        $columnindex = $request->columnindex;
        $value = $request->value;
        
        if($columnindex == "firstname"){
            $update["firstname"] = $value;
        }else if($columnindex == "lastname"){
            $update["lastname"] = $value;
        }else if($columnindex == "company_name"){
            $update["company_name"] = $value;
        }else if($columnindex == "email"){
            $update["email"] = $value;
        }else if($columnindex == "status"){
            $update["status"] = ($value == 'ACTIVE') ? 'INACTIVE' : 'ACTIVE' ;
        }else if($columnindex == "suspended"){
            $update["suspended"] = ($value == 'YES') ? 'NO' : 'YES' ;
        }

        try {
            $User_Update = Agent::where("id", $id)->update($update);
            if($User_Update){
                return response()->json(["status" => "success", "data" => "Update Sucessfully ", "error" => 0]);
            }
                
            return response()->json(["status" => "fail", "data" => "Something wrong", "error" => 0]);
        
        } catch (\Exception $e) {
            return response()->json(["status" => "fail", "data" => "Record not found", "error" => $e->getMessage()]);            
        }       
    }

    // agentedit page
    public function agentedit_update_ajex(Request $request){
        // return response()->json($request->all());
        $id = EncreptDecrept::decrept($request->id);
        // $id = $id[0];
        $id = $id;

        
        if($request->table == "agent"){
            
            // $data = $request->only('firstname', 'lastname',);        
            $validator = Validator::make($request->all(), [
                'firstname' => 'required|min:3',
                'lastname' => 'required|min:3',
                'contact_no' => 'required|digits:10',
                'address' => 'required|min:5',
                'state' => 'required|min:2',
                'city' => 'required|min:2',
                'postal_code' => 'required|min:5',
                'company_name' => 'required|min:3'             
            ]);
            if ($validator->fails()) {
                $data_responce = ["status" => "danger", "data" => "Validation error","error" => $validator->messages()];
                return response()->json($data_responce, 200);
            }

            // dd($request->all());  
            // dd($id);    
            $update["firstname"] = $request->firstname;
            $update["lastname"] = $request->lastname;
            // $update["email"] = $request->email;
            $update["contact_no"] = $request->contact_no;
            $update["address"] = $request->address;
            //$update["country"] = $request->country;
            $update["state"] = $request->state;
            $update["city"] = $request->city;
            $update["postal_code"] = $request->postal_code;
            $update["company_name"] = $request->company_name;
            // dd($update);
            $User_Update = Agent::where("id", $id)->update($update);

        }else if($request->table == "user"){

            $validator = Validator::make($request->all(), [
                'firstname_user' => 'required|min:3',
                'lastname_user' => 'required|min:3',
                'contact_no_user' => 'required|digits:10',                            
            ]);
            if ($validator->fails()) {
                $data_responce = ["status" => "danger", "data" => "Validation error","error" => $validator->messages()];
                return response()->json($data_responce, 200);
            }

            $update["firstname"] = $request->firstname_user;
            $update["lastname"] = $request->lastname_user;
            // $update["email"] = $request->email;
            $update["phoneno"] = $request->contact_no_user;
            // dd($update);
            $User_Update = User::where('role','AGENT')->where("tenant_id", $id)->update($update);

        }
        try {
            
            if($User_Update){
                return response()->json(["status" => "success", "data" => "Update Sucessfully ", "error" => 0]);
            }                
            return response()->json(["status" => "fail", "data" => "Something wrong", "error" => 0]);
        
        } catch (\Exception $e) {
            return response()->json(["status" => "fail", "data" => "Record not found", "error" => $e->getMessage()]);            
        }       
    }
    // public function agentcomission(){        
    //     return view('user.agentcomission');
    // }
    // public function agentcomissionajax($id, Request $request){
    //     $decrypted_id = EncreptDecrept::decrept($id);
    //     $agent_comission_list = AgentComission::where('agent_id',$decrypted_id);
    //     $fromdate = $request->get('fromdate') ? $request->get('fromdate') : '';
    //     $todate = $request->get('todate') ? $request->get('todate') : '';
    //     // $fromdate = '2021-10-13';
    //     // $todate = '2021-10-13';
    //     if(!empty($fromdate)){
    //         $agent_comission_list = $agent_comission_list->whereDate('created_date', '>=', $fromdate); 
    //         // $agent_comission_list = $agent_comission_list->where('created_date', '>=', $fromdate);
    //     }
    //     if(!empty($todate)){
    //         $agent_comission_list = $agent_comission_list->whereDate('created_date', '<=', $todate); 
    //         // $agent_comission_list = $agent_comission_list->where('created_date', '>=', $fromdate);
    //     }
         
    //     $agent_comission_list = $agent_comission_list->get();
    //     //dd($agent_comission_list);
    //    foreach($agent_comission_list as $data){
    //     $date = new DateTime($data->created_date);
        
    //     // $date_result = $date->format('Y-M-d H:i');
    //     $date_result = $date->format(Config('const.datepicker-format1'));
    //         $records[] = [
    //             'summary' => $data->summary,
    //             'amount' => $data->amount,
    //             'commission' => $data->commission_percentage,
    //             'debit' => $data->debit,
    //             'cradit' => $data->cradit,
    //             'balance' => $data->balance,
    //             'created' => $date_result,  
    //             'tenant' => $data->tenant_account_number,
    //             'id' =>   $id,            
    //         ];

    //    }

       
    //     $response['data'] = $records;    
    //     return response()->json($response);
        
        
    // }

    // agentedit page 
    public function agentedit($id, Request $request){
        // dd($id);
        $perpage = 3;
        $decrypted_id = EncreptDecrept::decrept($id);
        // $id = $decrypted_id[0];
        $id = $decrypted_id;
        $response['agent'] = Agent::where('id',$id)->first();
        // dd($id);
        // dd($response['agent']);
        $response['user'] = User::where('tenant_id',$id)->first();
        $response['billplan'] = AgentBillplan::select('bill_plan.name', 'bill_plan.type','bill_plan.id as bill_plan_id', 'agent_billplan.id as agent_billplan_id', 'agent_billplan.commission')->where('agent_id',$id)->leftjoin('bill_plan','bill_plan.id','agent_billplan.billplan_id')->where("agent_billplan.status","ACTIVE")->orderBy('agent_billplan.id', 'desc')->paginate($perpage);
        $response['billplan_list'] = BillPlan::get();
        $response['i'] = 1;
        if($request->ajax()){
            // dd($id);
            $response_part['i'] = $perpage * ($request->page - 1);
            if($request->addnewplan){
                $response_part['inew'] = "addnewplan";
                $response_part['i'] = 0;
            }

            $response_part['page'] = 'agentedit_billing';
            $response_part['billplan'] = $response['billplan'];
            // dd($id);
            $view = view('user.data', $response_part)->render();
            $response_ajex['html'] = $view;
           
            return response()->json($response_ajex);
        }        
        return view('user.agentedit',$response);        
    }
    // agentedit page ajex update bill plan comission
    public function agenteditbillplan_update_ajex(Request $request){
        $id = EncreptDecrept::decrept($request->id);
        $columnindex = $request->columnindex;
        $value = $request->value;
        
        
        $update[$columnindex] = $value;
        
        // dd($id);
        try {
            $User_Update = AgentBillplan::where("id", $id)->update($update);
            if($User_Update){
                return response()->json(["status" => "success", "data" => "Update Sucessfully ", "error" => 0]);
            }                
            return response()->json(["status" => "success", "data" => "Something wrong", "error" => 0]);
        
        } catch (\Exception $e) {
            return response()->json(["status" => "fail", "data" => "Record not found", "error" => $e->getMessage()]);            
        }       
    }

    // agentedit page add new bill plan 
    public function addbillplan_ajex(Request $request){
        $id = EncreptDecrept::decrept($request->id);
        $data = $request->only('billplan_id', 'commission');        
        $validator = Validator::make($data, [
            'billplan_id' => 'required',
            'commission' => 'required|digits_between:1,3',            
        ]);
        // dd($request->all());
        //Send failed response if request is not valid

        if ($validator->fails()) {
            $data_responce = ["status" => "danger", "data" => "Validation error","error" => $validator->messages()];
            return response()->json($data_responce, 200);
        }
        // dd($id);
        $status = 'ACTIVE';        
        $newplan = new AgentBillplan();
        // $newplan->agent_id = $id[0];
        $newplan->agent_id = $id;
        $newplan->billplan_id = $request->billplan_id;
        $newplan->commission = (int)$request->commission;
        $newplan->status = $status;
        if($newplan->save()){
            return response()->json(["status" => "success", "data" => "Update Sucessfully ", "error" => 0]);
        }            
        return response()->json(["status" => "danger", "data" => "Somthing Wrong ", "error" => 0]);
        

    }

    public function deleteData($id,$table,Request $request){
        $id = EncreptDecrept::decrept($id);
        
        $data['id'] = $id;
        $data['table'] = $table;        
        if($data['table'] == "agent_billplan"){
            $update['status'] = 'INACTIVE';
        }
        $User_Update = DB::table($data['table'])->where('id',$data['id'])->update($update);        
  
        if($User_Update){
            return response()->json(["status" => "success", "data" => "Record deleted sucessfully ".$User_Update, "error" => 0]);
        }
        return response()->json(["status" => "fail", "data" => "Somthing Wrong!", "error" => 0]);           
    }
}

