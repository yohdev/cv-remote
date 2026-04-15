<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if(!class_exists('vxg_pipedrive_api')){
    
class vxg_pipedrive_api extends vxg_pipedrive{
  
  public $token='' ; 
  public $url='' ; 
    public $info='' ; // info
    public $error= "";
    public $api_key= "";
    public $timeout= "70";

function __construct($info) {
     
    if(isset($info['data'])){ 
       $this->info= $info;
       if(!empty($info['data']['api_key'])){
        $this->api_key=$info['data']['api_key'];
            $this->url=trailingslashit($info['data']['app_url']).'v1/';
       }
    }
    }
public function get_token(){
  $users=$this->get_users();
    $info=$this->info;
    $info=isset($info['data']) ? $info['data'] : array();
    if(is_array($users) && !empty($users)){
    $info['valid_token']='true';    
    }else {
      unset($info['valid_token']); 
      if(!empty($users) && is_string($users)){
          $info['error']=$users;
      } 
    }
    return $info;

}
public function get_users(){
  $users=$this->post_crm('users','get'); 
$arr=array();
if(!empty($users['data'])){
  foreach($users['data'] as $k=>$v){
  $arr[$v['id']]=$v['name'];    
  }  
}else if(!empty($users['detail'])){
$arr=$users['detail'];  

}else if(!empty($users['error'])){
$arr=$users['error'];  

}
    return $arr;

}

public function get_crm_fields($module,$fields_type=false){

$fields=$this->post_crm($module.'Fields','get');
//var_dump($fields,$module); // die();

$res=array(); $standard=array('name','first_name','last_name','email','label','title','stage_id','value'); 
$skip=array('open_deals_count','activities_count','closed_deals_count','lost_deals_count','won_deals_count','next_activity_date','last_activity_date','update_time','done_activities_count','last_incoming_mail_time','email_messages_count','undone_activities_count','last_outgoing_mail_time','person_name','person_phone','person_email','org_name','org_address','source','product_amount','product_quantity');
if(!empty($fields['data'])){

if($module == 'lead'){
  $deal_fields=$this->post_crm('dealFields','get'); 
  if(!empty($deal_fields['data'])){
      foreach($deal_fields['data'] as $v){
         if(!empty($v['last_updated_by_user_id'])){
         $fields['data'][]=$v;    
         } 
      }
  }
   // 
} 
foreach($fields['data'] as $k=>$v){
   if($module == 'lead'){
    if($v['key'] == 'related_person_id'){
       $v['key']='person_id'; 
    }  if($v['key'] == 'related_org_id'){
       $v['key']='org_id'; 
    }if($v['key'] == 'labels'){
       $v['key']='label_ids'; 
    $arr=$this->post_crm('leadLabels','get');  
    if(!empty($arr['data'])){
   $v['options']=$arr['data'];
    } 
    }
    if($v['key'] == 'deal_value'){
       $v['key']='lead_value'; 
       $v['name']='Lead Value'; 
    }
    if($v['key'] == 'deal_currency'){
       $v['key']='lead_currency'; 
       $v['name']='Lead Currency'; 
    }
    if( in_array($v['key'],array('deal_id','add_time','archive_time') )){
      continue;  
    }
   }
    if(empty($v['id']) && strpos($v['key'],'_currency') === false ){
    continue;    
    }
    if(in_array($v['key'],$skip)){
        continue;
    }
 $label=$v['name'];
 if($v['key'] == 'person_id'){
 $label='Override Person ID';    
 }
 if($v['key'] == 'org_id'){
 $label='Override Organization ID';    
 }  
 
$field=array('label'=>$label,'name'=>$v['key'],'type'=>$v['field_type']);
 if(in_array($v['key'],array('name','title'))){ $field['req']='true';    } 
 if($v['key'] == 'lead_currency'){
  $field['eg']='3 digit currency (USD)';   
 } 
 if($v['key'] == 'stage_id'){
      $stages_arr=$this->post_crm('stages','get',array('limit'=>'500'));
      if(!empty($stages_arr['data'])){
      $ops=array(); 
      foreach($stages_arr['data'] as $vv){
      $ops[]=array('id'=>$vv['id'],'label'=>$vv['name'].' - '.$vv['pipeline_name']);      
      } 
     $v['options']=$ops;    
      }   
 }
 if(!empty($v['options'])){
     $field_options=array(); $egs=array();
   foreach($v['options'] as $op){
       if(!isset($op['label']) && isset($op['name'])){
           $op['label']=$op['name'];
       }
$field['options'][]=array('label'=>$op['label'],'value'=>$op['id']); 
$egs[]=$op['id'].'='.$op['label'];       
    }
$field['eg']=implode(', ',array_slice($egs,0,30));   
}
if(!in_array($v['key'],$standard)){
    $field['is_custom']='1';
}
 $res[$v['key']]=$field;   
 if($field['type'] == 'daterange'){
     $field['name']=$field['name'].'_until';
     $field['label']=$field['label'].' (End Date)';
  $res[$v['key'].'_until']=$field;   
 }
}
if($module != 'lead'){
$res['vx_attachments']=array('label'=>'Attachments - Related List','name'=>'vx_attachments','type'=>'files','maxlength'=>'0','is_custom'=>'1');  
$res['vx_attachments2']=array('label'=>'Attachments - Related List 2','name'=>'vx_attachments2','type'=>'files','maxlength'=>'0','is_custom'=>'1');  
$res['vx_attachments3']=array('label'=>'Attachments - Related List 3','name'=>'vx_attachments3','type'=>'files','maxlength'=>'0','is_custom'=>'1');  
$res['vx_attachments4']=array('label'=>'Attachments - Related List 4','name'=>'vx_attachments4','type'=>'files','maxlength'=>'0','is_custom'=>'1');  
$res['vx_attachments5']=array('label'=>'Attachments - Related List 5','name'=>'vx_attachments5','type'=>'files','maxlength'=>'0','is_custom'=>'1'); 
}

}else if(!empty($fields['detail'])){
$res=$fields['detail'];  
}
return $res;
}
public function verify_files($files,$old=array()){
        if(!is_array($files)){
        $files_temp=json_decode($files,true);
     if(is_array($files_temp)){
    $files=$files_temp;     
     }else if (!empty($files)){ //&& filter_var($files,FILTER_VALIDATE_URL)
      $files=array_map('trim',explode(',',$files));   
     }else{
      $files=array();    
     }   
    }
    if(is_array($files) && is_array($old) && !empty($old)){
   $files=array_merge($old,$files);     
    }
  return $files;  
}
public function push_object($module,$fields,$meta){ 
    
  //  $var=array('name'=>'large','prices'=>array( array('currency'=>'USD','price'=>10)) );
   // $post=array('name'=>'third product','code'=>'third','prices'=>array(array('price'=>'25','currency'=>'USD')),'product_variations'=>array( ));
 // $arr= $this->post_crm('products/7/variations','post',json_encode($var)); 
//$v=array('product_id'=>2,'item_price'=>20,'quantity'=>3);
//  $arr=$this->post_crm($module.'s/5/products','post',json_encode($v));
//  $arr= $this->post_crm('products/2','get'); 
// var_dump($arr); die();
//check primary key
 $extra=array();

   $files=array(); $link=""; $error="";
  for($i=1; $i<6; $i++){
$field_n='vx_attachments';
if($i>1){ $field_n.=$i; }
  if(isset($fields[$field_n]['value'])){
    $files=$this->verify_files($fields[$field_n]['value'],$files);
    unset($fields[$field_n]);  
  }
} 
 if( $module == 'deal' && isset($fields['owner_id'])){
     $fields['user_id']=$fields['owner_id'];
 }
  $debug = isset($_GET['vx_debug']) && current_user_can('manage_options');
  $event= isset($meta['event']) ? $meta['event'] : '';
  $id= isset($meta['crm_id']) ? $meta['crm_id'] : '';
  if($debug){ ob_start();}
if(isset($meta['primary_key']) && $meta['primary_key']!="" && isset($fields[$meta['primary_key']]['value']) && $fields[$meta['primary_key']]['value']!=""){    
$search=$fields[$meta['primary_key']]['value'];
$field=$meta['primary_key'];
if(isset($meta['fields'][$field]) && !empty($meta['fields'][$field]['is_custom'])){
 $field='custom_fields';   
}

$search_response=$this->post_crm($module.'s/search','get',array('term'=>$search,'fields'=>$field));
///var_dump($search_response,$field,$search); die();
if(!empty($search_response['data']['items'])){
  $items=$search_response['data']['items'];
  //$item=end($items);
  if(!empty($items[0]['item']['id'])){
  $id=$items[0]['item']['id'];  
  $search_response =$items[0]['item']; 
  }  
}

  if($debug){
  ?>
  <h3>Search field</h3>
  <p><?php print_r($field) ?></p>
  <h3>Search term</h3>
  <p><?php print_r($search) ?></p>
    <h3>POST Body</h3>
  <p><?php print_r($body) ?></p>
  <h3>Search response</h3>
  <p><?php print_r($res) ?></p>  
  <?php
  }

      $extra["body"]=$search;
      $extra["response"]=$search_response;
  }
  

     if(in_array($event,array('delete_note','add_note'))){    
  if(isset($meta['related_object'])){
    $extra['Note Object']= $meta['related_object'];
  }
  if(isset($meta['note_object_link'])){
    $extra['note_object_link']=$meta['note_object_link'];
  }
}

 $status=$action=$method=""; $send_body=true;
 $entry_exists=false;

$object_url='';
$is_main=false;
$post=$old=array();
if($id == ""){
    //insert new object
$action="Added";  $status="1"; $method='post';
if(empty($meta['new_entry'])){
$object_url=$module.'s';
$is_main=true;
}else{
    $status="6";
      $error='Record not found in CRM';
}
}else{
 $entry_exists=true;
    if($event == 'add_note'){
        if(!empty($fields['body']['value'])){
         $post['note']=$fields['body']['value'];   
        }

$action="Note Added"; $status="1";
$object_url='lists/'.$meta['related_object'].'/members/'.$id.'/notes';
$method='post';  
}else if(in_array($event,array('delete','delete_note'))){
 $send_body=false;
 $method='delete'; 
 $object_url='';
     if($event == 'delete_note' && !empty($meta['note_object_link'])){ 
  $object_url='lists/'.$meta['related_object'].'/members/'.$meta['note_object_link'].'/notes/'.$id;
     }else{
     $object_url='lists/'.$meta['object'].'/members/'.$id;
     }
     $action="Deleted";
  $status="5";  

  }else{
$action="Updated"; $status="2";    
if(empty($meta['update']) || !empty($meta['update_empty']) ){ 
 $is_main=true;
$object_url=$module.'s/'.$id;
 $method='put';
 if(!empty($meta['update_empty'])){
 $arr=$this->post_crm($object_url,'get'); 
 if(!empty($arr['data'])){
  foreach($arr['data'] as $k=>$v){
      if(isset($fields[$k]) && isset($fields[$k]['value'])){
          if(in_array($k,array('email','phone'))){
              if(isset($v[0]['value'])){
              $v=$v[0]['value'];    
              }else{
              $v='';    
              }
          }
          if($v!=''){ $old[$k]=$v; } 
      }
  }   
 }   
 }
 } }
}
//var_dump($fields,$arr); die();
if($is_main){

$crm_fields=array();
if(!empty($meta['fields'])){
  $crm_fields=$meta['fields'];  
}

if(is_array($fields) && count($fields)>0){
    foreach($fields as $k=>$v){
  if(!empty($crm_fields[$k]['type']) && !isset($old[$k])){     
    $type=$crm_fields[$k]['type']; 
$val=$v['value'];       
if($k == 'label_ids'){
    $val=array_filter(array_map('trim',explode(',',$val))); 
    
}
if(in_array($k,array('user_id','owner_id','org_id','person_id'))){
    $val=(int)$val;
}
if($type == 'addressss'){

}else if($type == 'date'){
$post[$k]=date('Y-m-d',strtotime($val));
}else if( in_array($type,array('enum')) && !in_array($k,array('marketing_status'))){
$post[$k]=(int)$val;
}else if( in_array($type,array('set')) && $k != 'label_ids' ){ 
   if($val!=''){ 
if(!is_array($val)){ $val=array($val); }
$val=array_map('intval',$val);
$post[$k]=$val;
   }
}else if( in_array($type,array('double','monetary'))){
$post[$k]=(float)$val;
}else{
   
$post[$k]=$val;      
}   }
}
//var_dump($post); die();
$name='';
if(isset($post['first_name'])){
  $name=$post['first_name'];
  unset($post['first_name']);  
}
if(isset($post['last_name'])){
  $name.=' '.$post['last_name'];
  unset($post['last_name']);  
}
$name=trim($name);
if(empty($post['name']) && !empty($name) ){
 $post['name']=$name;   
}

//$post['status']=!empty($meta['status']) ? $meta['status'] : 'subscribed';
//$post['email_type']=!empty($meta['email_type']) ? $meta['email_type'] : 'html';
//$post['language']=!empty($meta['language']) ? $meta['language'] : 'en';

} } 
//var_dump($post); die();

if(!empty($method) && !empty($object_url) && !empty($post) ){
    if($module == 'lead'){
        if(isset($post['org_id'])){
         $post['organization_id']=$post['org_id'];
         unset($post['org_id']);   
        } 
        if(isset($post['lead_value'])){
         $post['value']=array('amount'=>floatval($post['lead_value']),'currency'=>'USD');
if(!empty($post['lead_currency'])){
  $post['value']['currency']=$post['lead_currency'];
    unset($post['lead_currency']);
}
         unset($post['lead_value']);    
        }  
       // var_dump($post); //die();
   $post=json_encode($post);     
    }
$arr= $this->post_crm($object_url, $method, $post);
}
//var_dump($object_url,$arr,$post,$method); die();
if($module == 'lead'){
//var_dump($object_url,$arr,$post,$method); die();
}
if(!empty($arr['error'])){
       $status=''; $error=$arr['error'];
}else if(!empty($arr['data']['id'])){
$id=$arr['data']['id'];        

if(!empty($meta['order_items']) && $module == 'deal'){
   $order_res=$this->get_pipe_products($meta); 
   $zoho_products=$order_res['res']; 
   if(isset($order_res['count']) && !empty($zoho_products) && $order_res['count'] > count($zoho_products)){ //if some item failed , do not process order
    $method=''; $arr=array('code'=>'lines_missmatch','message'=>'Some Pipedrive line items failed');   
   } 
  
  if(is_array($order_res['extra'])){
  $extra=array_merge($extra, $order_res['extra']);
  }
  $fields['lines']=$zoho_products;
  foreach($zoho_products as $k=>$v){
  $extra['Add Line '.$k]=$this->post_crm($module.'s/'.$id.'/products','post',json_encode($v));     
  }
}

if(!empty($files)){ //$related['files']
 $camp_path='files';  
 $upload=wp_upload_dir();  
foreach($files as $k=>$file){
 $file=str_replace($upload['baseurl'],$upload['basedir'],$file);
 $id_name= $module == 'organization' ? 'org_id' : $module.'_id';
$file_post=array('attachments_v2'=>array($file),$id_name=>$id);
$extra['Add Files '.$k]=$this->post_crm($camp_path,'post',$file_post); 

} 
 
}

    }
  if($debug){
  ?>
  <h3>Account Information</h3>
  <p><?php //print_r($this->info) ?></p>
  <h3>Data Sent</h3>
  <p><?php print_r($post) ?></p>
  <h3>Fields</h3>
  <p><?php //echo json_encode($fields) ?></p>
  <h3>Response</h3>
  <p><?php print_r($response) ?></p>
  <h3>Object</h3>
  <p><?php print_r($module."--------".$action) ?></p>
  <?php
// echo  $contents=trim(ob_get_clean());
  if($contents!=""){
  update_option($this->id."_debug",$contents);   
  }
  }
       //add entry note
 if(!empty($meta['__vx_entry_note']) && !empty($id)){
 $disable_note=$this->post('disable_entry_note',$meta);
if(!($entry_exists && !empty($disable_note))){
$entry_note=$meta['__vx_entry_note'];
if(!empty($entry_note['body'])){
$note_post=array('content'=>$entry_note['body'],$module.'_id'=>$id);
$object_url='notes';
$note_response= $this->post_crm( $object_url,'post',$note_post);
  $extra['Note Body']=$entry_note['body'];
  $extra['Note Response']=$note_response;
}
   }  
 }

return array("error"=>$error,"id"=>$id,"link"=>$link,"action"=>$action,"status"=>$status,"data"=>$fields,"response"=>$arr,"extra"=>$extra);
}

public function get_wc_items($meta){
      $_order=self::$_order;
    //  $fees=$_order->get_shipping_total();
    //  $fees=$_order-> get_total_discount();
    //  $fees=$_order-> get_total_tax();

      
     $products=array();  $order_items=$items=array(); 
     
      if(is_object($_order) && method_exists($_order,'get_items')){
   $items=$_order->get_items(); 
 }
 
if(is_array($items) && count($items)>0 ){
foreach($items as $item_id=>$item){

$sku=$img_id=$cat=$var_name=''; $discount=$qty=$unit_price=$tax=$total=$cost=$cost_woo=$stock=0;
if(method_exists($item,'get_product')){
  // $p_id=$v->get_product_id();  
  
   $product=$item->get_product();
   if(!$product){ continue; } //product deleted but exists in line items of old order
        $total=floatval($item->get_total());
   $total=round($total,2);
   $qty = $item->get_quantity();  
   $tax = $item->get_total_tax();
   if(!empty($tax) && !empty($qty)){
       $tax=floatval($tax)/$qty;
   }
   $title=$product->get_title();
   
  // $title=$item->get_name();
   $sku=$product->get_sku();     
   $unit_price=floatval($product->get_price());  
   $unit_price=round($unit_price,2);  
    $parent_id=$product->get_parent_id();
    $product_id=$product->get_id(); 
    if(method_exists($_order,'get_item_total')){
        $discount=$_order->get_total_discount(); 
       $cost=(float)$_order->get_item_total($item,false,true); //including woo coupon discuont
       $cost_woo=(float)$_order->get_item_subtotal($item, false, true); // does not include coupon discounts
   
     if(!empty($meta['item_price_custom'])){
      $cost=(float)wc_get_order_item_meta($item->get_id(),$meta['item_price_custom'],true); 
     }   
       $cost=round($cost,2);
       $cost_woo=round($cost_woo,2);
    }
    if(method_exists($product,'get_stock_quantity')){
   $stock=$product->get_stock_quantity();
  $img_id=$product->get_image_id(); //
  $terms = get_the_terms( $product->get_id() , 'product_cat' );
  if(!empty($terms[0]->name)){
   $cat=$terms[0]->name;   
  }
}
    
   
   if(!empty($parent_id)){
         $product_simple=new WC_Product($parent_id);
         $sku=$product_simple->get_sku(); 
     // append variation names ,  $item->get_name() does not support more than 3 variation names
          $attrs=$product->get_attributes(); //$item->get_formatted_meta_data( '' )
            $var_info=array(); //var_dump($attrs,$product_id); die();
             if(is_array($attrs) && count($attrs)>0){
                 foreach($attrs as $attr_key=>$attr_val){   //var_dump($attr_val);
                 if(!is_object($attr_val)){
                    // $att_name=wc_attribute_label($attr_key,$product);
                     $term = get_term_by( 'slug', $attr_val, $attr_key );
                 if ( taxonomy_exists( $attr_key ) ) {
                $term = get_term_by( 'slug', $attr_val, $attr_key );
                if ( ! is_wp_error( $term ) && is_object( $term ) && $term->name ) {
                    $attr_val = $term->name;
                }    
            } 
            if(!empty($attr_val)){
            $var_info[]=$attr_val;
            }    
                 } }
             }
          if(!empty($var_info)){
          $var_name=implode(', ',$var_info);    
          } 
          $unit_price=0; //empty of variables in woo   
   }
    if(empty($sku)){
        $sku='wc-'.$product_id;
    }
   if(empty($total)){ $unit_price=0; }
 }

  $temp=array('sku'=>$sku,'unit_price'=>$unit_price,'title'=>wp_strip_all_tags($title),'qty'=>$qty,'tax'=>$tax,'total'=>$total,'cost'=>$cost,'cost_woo'=>$cost_woo,'qty_stock'=>$stock,'img_id'=>$img_id,'cat'=>$cat,'tax_id'=>'','var_name'=>$var_name,'discount'=>$discount);
          if(method_exists($product,'get_stock_quantity')){
  // $temp['stock']=$product->get_stock_quantity();
  
   if(!(!empty($meta['item_tax']) && $meta['item_tax'] == 'none')){ 
   $item_tax=$item->get_taxes(); //var_dump($item_tax); die();
if(!empty($item_tax['total'])){
    $tax=0;
foreach($item_tax['total'] as $tax_id=>$v){
$tax+=WC_Tax::get_rate_percent($tax_id);  //WC_Tax::_get_tax_rate(4);
} 
$temp['tax_id']=$tax;  
   }
   
}

} 
if(!empty($meta['item_desc'])){
    $temp['item_desc']=$this->process_tags($meta['item_desc'],$item);
}
     $order_items[]=$temp;     
      }
     } 
 // var_dump($order_items); die();   
   return $order_items;       
}
public function add_pipedrive_var($meta){ 

}
public function get_pipe_products($meta){ 
    
     $sales_response=array();  $extra=array();
     $items=$this->get_wc_items($meta); $items_count=0;
     $currency=!empty($meta['currency']) ? $meta['currency'] : 'USD';
     if(is_array($items) && count($items)>0 ){
         $n=0;  $items_count=count($items);
      foreach($items as $item){
          $n++; //var_dump($item); continue; 
        $search=$item['sku']; $field='code';
          if(!empty($meta['items_search']) ){
          $search=$item[$meta['items_search']]; $field='name';    
          }
    $id=$var_id='';  
         
    $search_post=array('term'=>$search,'fields'=>$field);  
$item_res=$this->post_crm('products/search','get',$search_post); 
$extra['Search item - '.$n]=$search_post; 
$extra['Response item - '.$n]=$item_res; 
if(!empty($item_res['data']['items'])){
 $id=$item_res['data']['items'][0]['item']['id'];  
 $extra['Response item - '.$n]=$item_res['data']['items'][0]['item']; 
 if(!empty($item['var_name'])){
     $arr= $this->post_crm('products/'.$id,'get');
     if(!empty($arr['data']['product_variations'])){
   foreach($arr['data']['product_variations'] as $var){
     if(!empty($var['name']) && $var['name'] == $item['var_name']){
     $var_id=$var['id'];    
     }  
   }
     } 
} 
}else{
   $post=array('name'=>$item['title'],'code'=>$item['sku'],'prices'=>array(array('price'=>$item['unit_price'],'currency'=>$currency)));
  $arr= $this->post_crm('products','post',json_encode($post));  
  $extra['POST item - '.$n]=$post;
  $extra['Create item Res - '.$n]=$arr;
 if(!empty($arr['data']['id'])){ $id=$arr['data']['id']; }  
    
}
 if(!empty($item['var_name']) && empty($var_id)){
      $var=array('name'=>$item['var_name'],'prices'=>array( array('price'=>$item['cost'],'currency'=>$currency)) );
  $arr=$this->post_crm('products/'.$id.'/variations','post',json_encode($var));  
    $extra['Variation item - '.$n]=$post;
  $extra['Create Var Res - '.$n]=$arr;
  if(!empty($arr['data']['id'])){ $var_id=$arr['data']['id']; }   
 }

if(!empty($id)){ 
$product_detail=array('product_id'=>$id,'item_price'=>$item['cost'],'quantity'=>$item['qty']);
if(!empty($var_id)){
  $product_detail['product_variation_id']=$var_id;  
}
if(!empty($item['item_desc'])){
  $product_detail['comments']=$item['item_desc'];  
}

if(!empty($item['tax'])){
  $product_detail['tax']=$item['tax'];  
}
  $product_detail['tax_method']=empty($meta['item_tax']) ? 'inclusive' : $meta['item_tax'];  
if(!empty($meta['item_discount']) && !empty($item['discount'])){
  $product_detail['discount']=$item['discount'];  
  $product_detail['discount_type']='amount';  
}
$sales_response[]=$product_detail;
}
 
      }
     }
   //  die('----');
     return array('res'=>$sales_response,'extra'=>$extra,'count'=>$items_count);
}

public function post_crm($path,$method='get',$body=''){
       
$url=$this->url.$path.'?api_token='.urlencode($this->api_key);   
if(is_array($body)&& count($body)>0)
{ 
       if($method == 'get'){
       $url.='&'.http_build_query($body);  
       $body='';  
       }
}
     $head=array(); 
       if(!empty($body) && !is_array($body)){
       $head['Content-Type']='application/json';   
       }
       
 //  $body['api_token']='7e603dc4e90346a1c2a4e2318cb704bed3b5abf2';    
if(!empty($body) && is_array($body) && isset($body['attachments_v2'])){
     $files = array(); 
if(!empty($body['attachments_v2'])){
$files=$body['attachments_v2'];
unset($body['attachments_v2']);
$file_name='file';
}
$boundary = wp_generate_password( 24 );
$delimiter = '-------------' . $boundary;
$head['Content-Type']='multipart/form-data; boundary='.$delimiter;
$body = $this->build_data_files($boundary, $body, $files,$file_name);
$head['Content-Length']=strlen($body);
//$head['Host']='crmperks-sandbox.pipedrive.com';
}    

       $args = array(
  'body' => $body,
  'headers'=> $head,
  'method' => strtoupper($method), // GET, POST, PUT, DELETE, etc.
  'timeout' => 30,
  );
  
  $response = wp_remote_request($url, $args);
  if(is_wp_error($response)) { 
  $error = $response->get_error_message();
  return array('detail'=>$error);
  }
  $body = wp_remote_retrieve_body($response);
$body=json_decode($body,true);

return $body;
}
public function build_data_files($boundary, $fields, $files, $file_name='attachments[]'){
    $data = '';
    $eol = "\r\n";

    $delimiter = '-------------' . $boundary;

    foreach ($fields as $name => $content) {
        $data .= "--" . $delimiter . $eol
            . 'Content-Disposition: form-data; name="' . $name . "\"".$eol.$eol
            . $content . $eol;
    }

    foreach ($files as $name => $file) {
    $name=basename($file);
   $content = file_get_contents($file);
        $data .= "--" . $delimiter . $eol
            . 'Content-Disposition: form-data; name="'.$file_name.'"; filename="'.$name.'"' . $eol
            . 'Content-Type: '.mime_content_type($file).$eol;
          // . 'Content-Transfer-Encoding: binary'.$eol;

        $data .= $eol;
        $data .= $content . $eol;
    }
    $data .= "--" . $delimiter . "--".$eol;


    return $data;
}
public function get_entry($module,$id){


$arr=$this->post_crm($module.'s/'.$id,'get');
if(!empty($arr['data']) && is_array($arr['data'])){
$arr=$arr['data'];
}

      return $arr;     
}
   
}
}
?>