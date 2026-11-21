<?php
namespace App\Http\Controllers;
use App\Models\Template;
use App\Models\Wedding;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DashboardController extends Controller {
 private function customer(Request $request): User { $id=$request->session()->get('wedding_customer_id'); $user=$id ? User::find($id) : null; if(!$user){ $user=User::create(['name'=>'Customer Undangan','email'=>'customer-'.Str::uuid().'@guest.markaspunokawan.local','password'=>Hash::make(Str::random(40)),'role'=>'customer']); $request->session()->put('wedding_customer_id',$user->id); } return $user; }
 public function index(Request $request){ $customer=$this->customer($request); $weddings=$customer->weddings()->with('template')->latest()->get(); return view('dashboard.index',compact('weddings')); }
 public function templates(){ return view('dashboard.templates',['templates'=>Template::where('is_active',true)->orderBy('sort_order')->get()]); }
 public function create(){ return view('dashboard.create',['templates'=>Template::where('is_active',true)->orderBy('sort_order')->get()]); }
 public function store(Request $request){ $data=$request->validate(['groom_name'=>'required|string|max:80','bride_name'=>'required|string|max:80','event_date'=>'nullable|date','template_id'=>'nullable|exists:templates,id','language_style'=>'required|in:islami,umum,jawa']); $base=Str::slug($data['groom_name'].'-'.$data['bride_name']); $data['slug']=$base.'-'.Str::lower(Str::random(5)); $data['status']='draft'; $wedding=$this->customer($request)->weddings()->create($data); return redirect('/wedding/dashboard/invitations/'.$wedding->id.'/edit')->with('success','Undangan draft berhasil dibuat.'); }
 public function edit(Request $request,Wedding $wedding){ abort_unless($wedding->user_id===$this->customer($request)->id,403); return view('dashboard.edit',compact('wedding')); }
 public function update(Request $request,Wedding $wedding){ abort_unless($wedding->user_id===$this->customer($request)->id,403); $data=$request->validate(['groom_name'=>'required|string|max:80','bride_name'=>'required|string|max:80','groom_parents'=>'nullable|string|max:180','bride_parents'=>'nullable|string|max:180','event_date'=>'nullable|date','opening_text'=>'nullable|string|max:1500','quote_text'=>'nullable|string|max:1500','language_style'=>'required|in:islami,umum,jawa']); $wedding->update($data); return back()->with('success','Perubahan tersimpan sebagai draft.'); }
}
