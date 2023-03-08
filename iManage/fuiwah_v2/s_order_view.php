<!DOCTYPE html>
<html lang="en">
<?php
session_start();
$client = '';
$userId = '';
$cust_code = '';
$orderStatus = '';
$isWarehouse = isset($_GET['isWarehouse']) ? $_GET['isWarehouse'] : 0;
$wh_code = isset($_GET['wh_code']) ? $_GET['wh_code'] : '';

if (isset($_SESSION['_order_status'])){
    $orderStatus=$_SESSION['_order_status'];
}

if(isset($_GET['userId']) && isset($_GET['client'])){

    $client         = $_GET['client'];
    
    $_SESSION['settings'] = $_GET['settings'];

    $isCustomerView = false;
    if(isset($_GET['cust_code'])){
        $isCustomerView = true;
        $cust_code = $_GET['cust_code'];
    } 

    if($isCustomerView){
         
    }else{
        require_once('getSalespersonList.php');    
        $userId         = $_GET['userId'];
        $salespersonid  = $userId;

        $param = array(
            "client"        =>$client,
            "salespersonid" =>$userId
        );
      
        $salesperson_param = array(
            "client"         =>  $client,
            "salespersonid"  =>  $salespersonid
        );


        // $customer_data = getCustomerList($param);
        // $customer_data = $customer_data["data"];
        // $customer_len  = count($customer_data);
        $salesperson_data = getSalespersonList($salesperson_param);
        $_SESSION['role_id'] = $salesperson_data['role_id'];
        $_SESSION['user_id'] = $userId;
        $salespersonid = $salesperson_data['role_id'] == 10 ? 0 : $salesperson_data['salesperson_id'];
        $salesperson_data = $salesperson_data["data"];
        $salesperson_len  = count($salesperson_data);
    }
    

}else{
    header('location:Errorpage.php');
}

?>


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3pro.css">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>EasyTech</title>
    <!-- <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous"> -->
</head>
<body>

<table  style="border:0px solid #ccc; width:100%;">
    <tr>
        <td style="align:left" colspan="3">
            <form id="input-form">
                <!-- <input placeholder="Order Date From - Order Date To" id="rangeDateFrom" type="button" class="button_add radius text" id="orderDate"/> -->

                <?php if($client != 'eurosteel' && $client != 'ideamaker'): ?>
                    <!-- <div class="divider"></div> -->
                    <input placeholder="Delivery Date From - Delivery Date To" id="rangeDateTo" type="button" class="button_add radius text" id="deliveryDate"/>
                    <input id="search_order" type="text" placeholder="Search within page..." class="button_add radius text" onkeyup="search(this.value)"/>
                <?php endif; ?>
            </form>
        </td>
    </tr>




    <?php
    if(!$isCustomerView){
        if($_SESSION['_salesperson_id'] && is_numeric($_SESSION['_salesperson_id'])){
            for($i = 0; $i < $salesperson_len; $i++){
                if ($salesperson_data[$i]['value'] == $_SESSION['_salesperson_id'])
                    $salespersonName= $salesperson_data[$i]['label'];
                if (strlen($salespersonName) > 30)
                    $salespersonName= substr($salespersonName, 0, 29) . '...';
            }
        }else {
            $salespersonName="Choose Salesperson";
    
        }
    }
    ?>
    <?php if(!$isCustomerView):?>
    <!-- <tr style="max-width:100%;overflow: hidden;text-overflow: ellipsis; ">
        <td>

            <?php
            if($salespersonid == '0' || !is_numeric($salespersonid) || $_SESSION['role_id'] == 10):
                ?>
                <div class="divider"></div>
                <input style="background: #147efb url('./images/customer.png') no-repeat 5px center; max-width:100%;" id="salespersonSelection" value="<?php
                if(is_numeric($_SESSION['_salesperson_id']) ){
                    echo $salespersonName;
                }else{
                    echo "Choose Salesperson";


                }
                ?>" type="button" class="customer_selection_button radius text_white""/>

                <div id="salespersonDropDwn" style="display:none;" class="dropdown-content radius">

                    <input type="text" placeholder="Search ..." id="salespersonSeachInp" onkeyup="filter_salesperson()">

                    <?php for($i = 0; $i < $salesperson_len; $i++):?>
                        <a class="choosenSalesperson" data-value="<?php echo $salesperson_data[$i]['value']?>" data-name="<?php $str = $salesperson_data[$i]['label'];
                        if (strlen($str) > 30)
                            $str = substr($str, 0, 29) . '...';
                        echo $str;?>">
                            <?php echo $salesperson_data[$i]['label']?>

                        </a>
                    <?php endfor;?>

                </div>
            <?php endif;?>
            <?php
            if(count($salesperson_data) === 0 && !$isCustomerView ):
                ?>
                <input value="Error Loading Salesperson" type="button" class="customer_selection_button radius text_white"/>
                <div class="divider"></div>

            <?php endif;?>
        </td>
    </tr> -->
    <?php endif;?>






    <!-- <?php

    if(!$isCustomerView){
        if ($_SESSION['_customer_name'] ){
            $customerName=$_SESSION['_customer_name'];
            if (strlen($customerName) > 30){
                $customerName = substr($customerName, 0, 29) . '...';
            }

        }else{
            $customerName="Choose Customer";
        }
    }

    if(!$isCustomerView ): ?>

    <tr style="max-width:100%;overflow: hidden;text-overflow: ellipsis; ">
        <td>

            <div id="cust-load" class="loading-customer-div"><div class="loader-customer"></div></div>

            <input style="max-width:100%;" id="custSelection" value="<?php echo $customerName ?>" type="button" class="customer_selection_button radius text_white"/>

            <div id="customerDropDwn" style="display:none;" class="dropdown-content radius">

                <input type="text" placeholder="Search.." id="customerSeachInp" onkeyup="filter_cust()">




            </div>

        </td>
    </tr>
    
    <?php endif;?> -->

    <tr style="max-width:100%;overflow: hidden;text-overflow: ellipsis; ">
        <td>
            <select name="orderSelection" id="orderSelection" style="line-height:25px;" class="customer_selection_button radius text_white">
                <option <?php if ($orderStatus == '') echo 'selected' ; ?> value="">All Orders</option>
                <option <?php if ($orderStatus == '0') echo 'selected' ; ?> value="0">Active Orders</option>
                <option <?php if ($orderStatus == '1') echo 'selected' ; ?> value="1">Confirmed Orders</option>
                <option <?php if ($orderStatus == '2') echo 'selected' ; ?> value="2">Transferred Orders</option>
                <option <?php if ($orderStatus == '3') echo 'selected' ; ?> value="3">Posted Orders</option>
            </select>
        </td>
    </tr>

    
    <?php

    if ($_SESSION['_case_name'] ){
        $caseName=$_SESSION['_case_name'];
    }else{
        $caseName='';
    }

    if ($_SESSION['_status_name'] ){
        $statusName=$_SESSION['_status_name'];
    }else{
        $statusName='PENDING';
    }

    if ($_SESSION['_title_name'] ){
        $titleName=$_SESSION['_title_name'];
    }else{
        $titleName='';
    }

    if ($_SESSION['_ship_via'] ){
        $shipVia=$_SESSION['_ship_via'];
    }else{
        $shipVia='';
    }

    ?>

    <?php if($client == 'eurosteel'): ?>

    <tr style="max-width:100%;overflow: hidden;text-overflow: ellipsis; ">
        <td>
            <select name="shipViaSelection" id="shipViaSelection" style="line-height:25px;" class=" radius text_white">
                <option <?php if ($shipVia == '') echo 'selected' ; ?> value="">All Ship Via</option>
                <option <?php if ($shipVia == 'BW') echo 'selected' ; ?> value="BW">BW</option>
                <option <?php if ($shipVia == 'JB') echo 'selected' ; ?> value="JB">JB</option>
                <option <?php if ($shipVia == 'NONE') echo 'selected' ; ?> value="NONE">None</option>
            </select>
        </td>
    </tr>

    <tr style="max-width:100%;overflow: hidden;text-overflow: ellipsis; ">
        <td>
            <select name="titleSelection" id="titleSelection" style="line-height:25px;" class=" radius text_white">
                <option <?php if ($titleName == '') echo 'selected' ; ?> value="">All Job Type</option>
                <option <?php if ($titleName == 'CUTTING') echo 'selected' ; ?> value="CUTTING">Cutting</option>
                <option <?php if ($titleName == 'MACHINING') echo 'selected' ; ?> value="MACHINING">Machining</option>
                <!-- <option <?php if ($titleName == 'SEND TO TRANSPORT') echo 'selected' ; ?> value="SEND TO TRANSPORT">Send To Transport</option>
                <option <?php if ($titleName == 'READY FOR DELIVERY') echo 'selected' ; ?> value="READY FOR DELIVERY">Ready For Delivery</option> -->
            </select>
        </td>
    </tr>

    <tr style="max-width:100%;overflow: hidden;text-overflow: ellipsis; ">
        <td>
            <select name="statusSelection" id="statusSelection" style="line-height:25px;" class=" radius text_white">
                <option <?php if ($statusName == 'ALL') echo 'selected' ; ?> value="ALL">All Status</option>
                <option <?php if ($statusName == 'PENDING') echo 'selected' ; ?> value="PENDING">Pending</option>
                <option <?php if ($statusName == 'COMPLETED') echo 'selected' ; ?> value="COMPLETED">Completed</option>
                <option <?php if ($statusName == 'SEND TO TRANSPORT') echo 'selected' ; ?> value="SEND TO TRANSPORT">Send To Transport</option>
            </select>
        </td>
    </tr>

    <tr style="max-width:100%;overflow: hidden;text-overflow: ellipsis; ">
        <td>
            <input style="min-width:100%;border:1px solid #147efb;line-height:30px;color:#147efb;padding-left:5px;" id="caseSelection" name="caseSelection" type="text" value ="<?php echo $caseName ?>" class="radius"  placeholder="Search.."/>
        </td>
    </tr>

    <?php endif;?>

    <tr>
        <td align="middle">
            <div class="row">
                <div class="column" style="line-height:25px;padding-right:5px">
                    <div class="check">
                        <label class="checkbox_rounded">
                            <input type="checkbox" id="showCancelBox">
                            <div class="checkbox_hover"></div>
                        </label>
                        <p class="non_important_text" style="margin-left:5px;" >View CXL</p>
                    </div>
                </div>

                <div class="column" style="padding-right:5px">
                    <button class="radius non_important_text red_button text_white" id="btnClear"> Clear </button>
                </div>
                <div class="column">
                    <button class="radius non_important_text buttons text_white" id="btnSearch"> Search </button>
                </div>
            </div>
        </td>
    </tr>

</table>

<div class="divider"></div>
<div class="divider"></div>

<div class="divider"></div>
<div class="divider"></div>

<div id="ordersDiv">

</div>


<link href='https://fonts.googleapis.com/css?family=Open Sans' rel='stylesheet'>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.2.3/themes/airbnb.css">
<script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.3.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.2.3/flatpickr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery.redirect@1.1.4/jquery.redirect.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8.5.0/dist/sweetalert2.all.min.js"></script>
<!-- <link href='css/magnific-popup.css' rel='stylesheet'>
<script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.3.1.min.js"></script>
<script type="text/javascript" src="js/jquery.magnific-popup.min.js"></script> -->
<script>
const toString = Object.prototype.toString;

function tagType(a){
    if(a === null){
        return a === undefined ? '[object Undefined]' : '[object Null]';
    }
    return toString.call(a);
}

function isSymbol(a){
    const typeOfvar = typeof(a);
    return typeOfvar === 'symbol' ||
            (typeOfvar === 'object' && 
                a !== null && 
                tagType(a) === '[object Symbol]'
            );
}

function isObject(a){
    const typeOfvar = typeof(a);
    return a !== null && 
        (typeOfvar === 'object' || 
        typeOfvar === 'function'); 
}

function isNull(a){
    return a === null;
}

function isUndefined(a){
    return a === undefined;
}

function isEmpty(a){
    const typeOfvar = typeof(a);
    return a === null 
            ||a === undefined 
            ||a === 'undefined'
            ||a === 'null'
            ||a === '[object Undefined]'
            ||a === '[object Null]'
            ||typeOfvar !== 'string'
            || (typeOfvar === 'string' && String(a).trim().length === 0);
}

function isString(a){
    return typeof(a) === 'string';
}
</script>
<script>
const EMPTY = '';
const NAN = 0 / 0;
const reTrim = /^\s+|\s+$/g;
const reIsBadHex = /^[-+]0x[0-9a-f]+$/i;
const reIsBinary = /^0b[01]+$/i;
const reIsOctal = /^0o[0-7]+$/i;
const freeParseInt = parseInt;

class JString extends String{
    /**
     * Custom String()
     * @param { String } str 
     * @example var str = new JString('your string')
     *          // to check if string is valid
     *          str.isValid()
     *          // to convert json string to json object
     *          var json = new JString('{"key":"11","value":"Hola"}')
     *              json = json.isValid() ? json.toJson() : empty;
     *          // to get a data from json
     *          var key = json.pick('key')
     *          // to convert to Float
     *          var num = new JString('10.20');
     *              num = num.isValid() ? num.toNumber() : 0
     */
    constructor(props) {
        props = isUndefined(props) ? EMPTY : props;
        props = isNull(props) ? EMPTY : props;
        props = props === 'null' ? EMPTY : props;
        props = props === 'undefined' ? EMPTY : props;
        props = isNaN(props) === false ? String(props) : props;
        super(props);
        this.init = props;
        this.str = props || EMPTY;
        this.json = null;
    }

    isValid(){
        return isString(this.str) && !isUndefined(this.str) && !isEmpty(this.str);
    }

    isEmail(){
        var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(this.str).toLowerCase());
    }

    isFormSafe(){
        var format = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]+/;
        return !(format.test(this.str));
    }

    isPassword(minimumCharLen = 5){
        return (!(/[</\(^*|\.,'":;)>]/.test(this.str))) &&
                this.str.length > (minimumCharLen-1);
    }

    isNumber(){
        return isNaN(this.str) === false;
    }

    toNumber(){
        var a = this.str;
        if(isSymbol(a)){
            return NAN;
        }
        if(isObject(a)){
            const other = typeof a.valueOf === 'function' ? a.valueOf() : value;
            a = isObject(other) ? `${other}` : other;
        }
        if(isString(a)){
            return a === 0 ? a : +a;
        }
        a = a.replace(reTrim, '');
        const isBinary = reIsBinary.test(a);
        return (isBinary || reIsOctal.test(a))
            ? freeParseInt(value.slice(2), isBinary ? 2 : 8)
            : (reIsBadHex.test(a) ? NAN : +a);
    }

    isJson(){
        if(!this.isValid()){
            return false;
        }
        if(this.str == 0 || this.toNumber() > 0){
            return false;
        }
        try {
            this.json = JSON.parse(this.str);
            return this;
        } catch (error) {
            this.json = null;
            return false;
        }
    }

    inCurrency(currency = 'RM') {
        var num = this.toNumber();
        return currency + num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
    }

    rollback(){
        this.str = this.init;
        return this;
    }

    replaceInfinity(find = '', replace = ''){
        var re  = new RegExp(find, 'g');
        this.str = this.str.replace(re, replace);
        return this;
    }

    removeSpecialChar(){
        this.str = this.replaceInfinity(" ","");
        this.str = this.str.replace(/[`~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi, '');
        return this;
    }

    removeLastChar(character = ''){
        this.str = String(this.str).trim(); 
        if(this.str.length > 0){
            let lastChar = this.str.charAt(this.str.length - 1);
            if(character && lastChar === character){
                this.str = this.str.substr(0,this.str.length - 1);
            }
            if(!character){
                this.str = this.str.substr(0,this.str.length - 1);
            }
        }
        return this;
    }

    removeFirstChar(character = ''){
        this.str = String(this.str).trim(); 
        if(this.str.length > 0){
            let firstChar = this.str.charAt(0);
            if(character && firstChar === character){
                this.str = this.str.substr(1,this.str.length - 1);
            }
            if(!character){
                this.str = this.str.substr(1,this.str.length - 1);
            }
        }
        return this;
    }

    searchDeep(src = ''){
        return !!~this.str.search(new RegExp(src, 'i'));
    }

    searchTokens(tokens = [], matchAny = false){
        var found = 0;
        for (let index = 0; index < tokens.length; index++) {
            const piece = tokens[index];
            if(!!~this.str.search(new RegExp(piece, 'i'))){
                found++;
            }
        }
        if(matchAny){
            return (found >= tokens.length && found != 0);
        }
        return found != 0;
    }

    toStr(){
        return this.str;
    }

    toJson(){
        if(isNull(this.json)){
            this.isJson();
        }
        return this.json;
    }

    instanceOfJString(){
        return this;
    }

    instanceOfString(){
        return String(this.str);
    }

    isSafe(){
        if(this.isValid()){
            return this.str;
        }
        return EMPTY;
    }

    trimHtml(){
        return this.str.replace(/<\/?[^>]+(>|$)/g, "");
    }

    firstUpperCase(){
        if(this.isValid()){
            this.str = this.str.charAt(0).toUpperCase() + this.str.slice(1);
        }
        return this;
    }

    firstLowerCase(){
        if(this.isValid()){
            this.str = this.str.charAt(0).toLowerCase() + this.str.slice(1);
        }
        return this;
    }

    lastUpperCase(){
        if(this.isValid()){
            this.str = this.str.slice(0,this.str.length-1) + this.str.charAt(this.str.length-1).toUpperCase();
        }
        return this;
    }

    lastLowerCase(){
        if(this.isValid()){
            this.str = this.str.slice(0,this.str.length-1) + this.str.charAt(this.str.length-1).toLowerCase();
        }
        return this;
    }

    isEqual(compare){
        if(this.isValid()){
            return this.str.trim() === String(compare).trim();
        }
        return false;
    }

    isEqualDeep(compare){
        if(this.isValid()){
            return this.str.trim().toLowerCase() === String(compare).trim().toLowerCase();
        }
        return false;
    }

    toArray(){
        if(this.isValid()){
            var final = [];
            this.walk((char,index)=>{
                final.push(char);
            });
            return final;
        }
        return [];
    }

    walk(cb){
        for (let index = 0; index < this.str.length; index++) {
            const char = this.str[index];
            if(cb(char,index) === false){
                break;
            }
        }
    }

    occurrenceOf(piece, ignoreCase = false){
        if(piece && piece.length > 1){
            return 0;
        }
        var counter = 0;
        this.walk((char,index)=>{
            counter += 
                ignoreCase === false ? 
                    (char == piece ? 1 : 0) :
                    (char.toLowerCase() === piece.toLowerCase() ? 1 : 0);
        });
        return counter;
    }
}
function str(props){
    return new JString(props);
}
</script>
<script>
    var fullOrders = [];
    var filter_cust,filter_salesperson,custSelection,updateOrderApprovalStatus;

    class Order{
        constructor(
            search_order,
            userId,
            client,
            orderFrom,
            orderTo,
            deliverFrom,
            deliverTo,
            customer_id,
            backlink,
            salesperson_id,
            customer_name,
            customer_code,
            case_name,
            status_name,
            title_name,
            isCustomerView,
            isWarehouse,
            ship_via,
            order_status,
            wh_code
        ){
            this.search_order = search_order;
            this.userId = userId;
            this.client = client;
            this.orderFrom = orderFrom;
            this.orderTo = orderTo;
            this.deliverFrom = deliverFrom;
            this.deliverTo = deliverTo;
            this.customerId = customer_id;
            this.backlink   = backlink;
            this.salesperson_id = salesperson_id;
            this.customer_name  = customer_name;
            this.customer_code  = customer_code;
            this.case_name  = case_name;
            this.status_name  = status_name;
            this.title_name  = title_name;
            this.isCustomerView  = isCustomerView;
            this.isWarehouse = isWarehouse;
            this.ship_via = ship_via;
            this.order_status = order_status;
            this.wh_code = wh_code;
        }
    }

    function search(query){
        //el.innerHTML.split('<')[0].replace('| C','').replace('&amp;','')
        if(query){
            var result = [];
            var fullDivs = document.getElementsByClassName('parent-div');
            for (let i = 0; i < fullDivs.length; i++) {
                const div = fullDivs[i];
                const nested = div.children[0].children;
                for (let j = 0; j < nested.length; j++) {
                    const child = nested[j];
                    const text = child.innerHTML.split('<')[0].replace('| C','').replace('&amp;','');
                    if(str(text).searchTokens(String(query).split(' '),true)){
                        result.push(div);
                        break;
                    }
                }
            }
            $("#ordersDiv").empty();
            for (let i = 0; i < result.length; i++) {
                const div = result[i];
                $("#ordersDiv").append(div);
            }
        }else{
            $("#ordersDiv").empty();
            for(let i = 0, len = fullOrders.length; i < len; i++){
                let view = fullOrders[i];
                $("#ordersDiv").append(view);
                if(localStorage.getItem("transfer_order_scrollTo")){
                    $("html, body").animate({ scrollTop: localStorage.getItem("transfer_order_scrollTo") });
                }
            }
        }
    }

    function showMessage(msg){
        alert(msg);
    }

    $(document).ready(function(){

        var tisenoAPI = 'http://13.229.126.174/Delivery/api.php?action=submitInformation';

        $("#customerDropDwn").removeAttr("style");

        $(document).on('click', '.create-button', function() {
            let orderData     = JSON.parse($(this).attr('data-json'));
            $.redirect(tisenoAPI, orderData);
        });

        $(document).on('click', '.view-button', function() {
            let orderData     = JSON.parse($(this).attr('data-json'));
            $.redirect(tisenoAPI, orderData);
        });

        $(document).on('click', '.approve-button', function() {
            // approve here
            var order_id = $(this).attr('data-orderid');
            var salesperson_id = $(this).attr('data-userid');
            var client = $(this).attr('data-client');

            var data = encodeURIComponent('{"client":"' + client + '","salesperson_id":"' + salesperson_id +'","order_id":"' + order_id +'","action":"approve","comment":""}');
            Swal.fire({
                title: 'Approving Document',
                html: 'Please wait...',
                onBeforeOpen: () => {
                    Swal.showLoading()
                },
                onClose: () => {
                    
                }
            })
            updateOrderApprovalStatus(data);
        });

        $(document).on('click', '.reject-button', async function() {
            // reject here
            var order_id = $(this).attr('data-orderid');
            var salesperson_id = $(this).attr('data-userid');
            var client = $(this).attr('data-client');
            
            const {value: formValues} = await Swal.fire({
                html:
                    '<div data-orderid="'+order_id+'" data-userid="'+salesperson_id+'" data-client="'+client+'" class="container" id="swal-container">' +
                    '    <div class="row">' +
                    '        <div class="col">' +
                    '            <div>' +
                    '                <div class="card-body">' +
                    '                        <div class="form-group">' +
                    '                            <input id="reject_comment" class="swal2-input" autofocus placeholder="Reason to reject">' +
                    '                        </div>' +
                    '                        <div class="mx-auto">' +
                    '                        <button type="submit" class="reject-button-st" id="btn_reject">Reject</button></div>'+
                    '                </div>' +
                    '            </div>' +
                    '        </div>'+
                    '    </div>' +
                    '</div>',
                focusConfirm: true,
                background:'white',
                showConfirmButton:false,
                showCloseButton:true,
                preConfirm: () => {
                    return [
                        document.getElementById('en_remark').value
                    ]
                },
                onOpen: () => {
                    
                }
            });
        });

        $('body').on('click', '#btn_reject', function() {
            Swal.showLoading();

            var order_id = $("#swal-container").attr('data-orderid');
            var salesperson_id = $("#swal-container").attr('data-userid');
            var client = $("#swal-container").attr('data-client');
            var comment = $("#reject_comment").val();

            var data = encodeURIComponent('{"client":"' + client + '","salesperson_id":"' + salesperson_id +'","order_id":"' + order_id +'","action":"reject","comment":"'+comment+'"}');

            updateOrderApprovalStatus(data);
        });

        // today's orders

        $(window).scroll(function () {
            localStorage.setItem("transfer_order_scrollTo", $(window).scrollTop());
        });

        var backlink             = window.location.href;

        // today's orders

        var customer_name = "", customer_id = "", orderDatePicker_val = "", deliveryDatePicker_val = "";


        var userId                  = '<?php echo $userId; ?>';
        var client                  = '<?php echo $client; ?>';
        var salesperson_id          = '<?php echo $salespersonid; ?>';
        var customer_code           = '<?php echo $cust_code; ?>';
        var case_name               = '<?php echo $caseName; ?>';
        var status_name             = '<?php echo $statusName; ?>';
        var title_name              = '<?php echo $titleName; ?>';
        var ship_via                = '<?php echo $shipVia; ?>';
        var isWarehouse             = '<?php echo $isWarehouse; ?>';
        var order_status            = '<?php echo $orderStatus; ?>';
        var wh_code                 = '<?php echo $wh_code ?>';
        var search_order            = '<?php echo $search_order ?>';

        var isSalesPersonView = userId>0;
        var isCustomerView    = '<?php echo $isCustomerView; ?>';

        loadCustomers (client,salesperson_id);

        var orderFrom           = phpSessionExists('_odate_from');
        var orderTo             = phpSessionExists('_odate_to');

        var deliverFrom         = phpSessionExists('_ddate_from');
        var deliverTo           = phpSessionExists('_ddate_to');
        customer_id             = phpSessionExists('_cust_id');
        var view_cancel         = phpSessionExists('_view_cancel');


        if(!isCustomerView){
            if(phpSessionExists('_salesperson_id')){
            salesperson_id      = phpSessionExists('_salesperson_id');
            $("#salespersonDropDwn").data('clicked', true);
            loadCustomers (client,salesperson_id);

        }
        // if(!customer_name){
            customer_name           = phpSessionExists('_customer_name');
        // }
        }

        if(view_cancel){
            $('#showCancelBox').prop('checked', true);
        }

        loadOrders(new Order
            (search_order, userId,client,orderFrom,orderTo,deliverFrom,deliverTo,customer_id,backlink,salesperson_id,customer_name,customer_code,case_name,status_name,title_name,isCustomerView,isWarehouse,ship_via,order_status,wh_code)
        );

        const orderDatePicker = flatpickr("#rangeDateFrom",{
            mode: 'range',
            dateFormat: "Y-m-d",
            disableMobile: "false",
            defaultDate:[orderFrom,orderTo],
            onChange: function(selectedDates, dateStr, instance) {
                orderDatePicker_val = dateStr;
            },
            onReady: function(dObj, dStr, fp, dayElem){
                orderDatePicker_val = dStr;
            }
        });
        let clicks = 0;
        $("#rangeDateFrom").click(function(){
            clicks += 1;
            if(clicks%2==0 && clicks>0){
                orderDatePicker.close();
            }
        });

        const deliveryDatePicker = flatpickr("#rangeDateTo",{
            mode: 'range',
            dateFormat: "Y-m-d",
            disableMobile: "false",
            defaultDate:[deliverFrom,deliverTo],
            onChange: function(selectedDates, dateStr, instance) {
                deliveryDatePicker_val = dateStr;
            },
            onReady: function(dObj, dStr, fp, dayElem){
                deliveryDatePicker_val = dStr;
            }

        });

        let deliveryClicks = 0, salesperson_name;

        $("#rangeDateTo").click(function(){
            deliveryClicks += 1;
            if(deliveryClicks%2===0 && deliveryClicks>0){
                deliveryDatePicker.close();
            }
        });

        $("#rangeDateTo").blur(function(){
            if(deliveryDatePicker_val){
                deliveryDatePicker.toggle();
            }
        });

        $(".choosenCustomer").click(function() {

            customer_name = $(this).attr("data-name");
            customer_code   = $(this).attr("data-value");

            $("#custSelection").val(customer_name);
            toggle_cust_selection();
        });

        $("#custSelection").click(function() {
            /* if(!$("#salespersonDropDwn").data('clicked') && !isSalesPersonView )
            {
                alert("please select a salesperson first");
                return;
            } */
            toggle_cust_selection();
        });

        $("#salespersonSelection").click(function() {
            $("#customerDropDwn").hide();
            $("#salespersonDropDwn").toggle();
        });

        $(".choosenSalesperson").click(function() {

            $("#salespersonDropDwn").data('clicked', true);

            customer_code='';
            $("#custSelection").val("Choose Customer");

            salesperson_name = $(this).attr("data-name");
            salesperson_id = $(this).attr("data-value");

            $("#salespersonSelection").val(salesperson_name);

            loadCustomers (client,salesperson_id);
            toggle_sp_selection();
        });

        $('#titleSelection').on('change', function() {
            title_name = $(this).val();
        });

        $('#shipViaSelection').on('change', function() {
            ship_via = $(this).val();
        });
        
        $('#statusSelection').on('change', function() {
            status_name = $(this).val();
        });

        $('#caseSelection').on('change', function() {
            case_name = $(this).val();
        });

        $('#orderSelection').on('change', function() {
            order_status = $(this).val();
        });

        $('#search_order').on('change', function(){
            search_order = $(this).val();
        });

        $("#btnSearch").click(function() {

            var userId              = '<?php echo $userId; ?>';
            var client              = '<?php echo $client; ?>';
            var isWarehouse         = '<?php echo $isWarehouse; ?>';
            var wh_code             = '<?php echo $wh_code ?>';

            let orderDate_arr       = orderDatePicker_val.split("to");
            let deliveryDate_arr    = deliveryDatePicker_val.split("to");

            var orderFrom           = orderDate_arr[0];
            var orderTo             = orderDate_arr[1];

            var deliverFrom         = deliveryDate_arr[0];
            var deliverTo           = deliveryDate_arr[1];

            loadOrders(new Order
                (search_order, userId,client,orderFrom,orderTo,deliverFrom,deliverTo,customer_id,backlink,salesperson_id,customer_name,customer_code,case_name,status_name,title_name,isCustomerView,isWarehouse,ship_via,order_status,wh_code)
                ,true);
        });

        $("#btnClear").click(function() {

            $('#showCancelBox').prop('checked', false);
            $('#input-form')[0].reset();
            $('#custSelection').val("Choose Customer");
            $("#rangeDateFrom").attr("placeholder", "Order Date From - Order Date To");

            $.ajax({
                url: "clearSession.php?action=clearSession",
                type: "POST",
                contentType: false,
                cache: false,
                processData:false,
                success: function(response){
                    console.warn("session cleared"+response);

                    location.reload();
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.warn(xhr);
                }
            });

        });

        /* $('select').on('change', function() {
            var userId              = '<?php echo $userId; ?>';
            var client              = '<?php echo $client; ?>';

            let orderDate_arr       = orderDatePicker_val.split("to");
            let deliveryDate_arr    = deliveryDatePicker_val.split("to");

            var orderFrom           = orderDate_arr[0];
            var orderTo             = orderDate_arr[1];

            var deliverFrom         = deliveryDate_arr[0];
            var deliverTo           = deliveryDate_arr[1];

            loadOrders(new Order (
                userId,client,orderFrom,orderTo,deliverFrom,deliverTo,customer_id,salesperson_id,customer_name,customer_code
                )
            );
        }); */

        custSelection = function custSelection(value,name){
            customer_name = name;
            customer_id   = value;

            let tmp_cust_name = customer_name.length<29?customer_name:customer_name.substr(0,40)+' ...';
            $("#custSelection").val(tmp_cust_name);
            toggle_cust_selection();
        };

        updateOrderApprovalStatus = function updateOrderApprovalStatus (data){
            jQuery.ajax({
                type: "POST",
                url: "./route.php",
                data: "data=" + data + "&action=updateOrderApprovalStatus",
                success: function (msg) {
                    console.warn(JSON.stringify(msg));
                    debugger;
                    window.location.reload();
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.warn(xhr);
                }

            });
        }

        function loadCustomers (client,userId = (<?php echo $_GET['userId'];?> || 0)){

            $('#custSelection').hide();
            $('#cust-load').show();

            var data = encodeURIComponent('{"client":"' + client + '","salesperson_id":"' + userId +'"}');
            jQuery.ajax({
                type: "POST",
                url: "./route.php",
                data: "data=" + data + "&action=loadCustomers",

                success: function (msg) {

                    $('#custSelection').show();
                    $('#cust-load').hide();
                    
                    msg = isJSONparsable(msg);


                    if(msg){
                        customerList=msg.data;
                        cust_len=customerList.length;
                        $(".choosenCustomer").remove();
                        for(let i=0; i<cust_len; i++){
                            $("#customerDropDwn").append(`<a class="choosenCustomer" onclick="custSelection('${customerList[i].value}','${customerList[i].label.replace("'", "\\'").replace(/<\/?[^>]+(>|$)/g, "")}')">${customerList[i].label}</a>`);
                        }
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.warn(xhr);
                }

            });
        }

        function warn(msg){
            console.warn(JSON.stringify(msg));
        }

        function loadOrders(_, btnSearchClicked = false) {
            document.getElementById('btnSearch').disabled = 'true';
            document.getElementById('btnClear').disabled = 'true';
            
            $('#ordersDiv').empty();
            $('#ordersDiv').append('<div style="margin-left:-35px" class=" no_orders loader"></div>');

            var cancel      = $("#showCancelBox").is(':checked');

            cancel          = String((cancel | 0));

            if(_.isWarehouse == 1){
                var orderType   = 2;
            }else{
                var orderType   = _.order_status;
            }

            fullOrders = [];

            var doc_type    = '<?php echo $_GET["doc"]?>';
            console.warn(JSON.stringify(_));
            var data = encodeURIComponent('{"searchOrders_data" :{ "searchinput":"' + _.search_order + '", "salespersonId":"' + _.userId + '","showCancel":"' + cancel
                + '","dateFrom":"' + _.orderFrom + '","dateTo":"' + _.orderTo + '","order_status":"'
                + orderType+ '","customer_status":"' + _.customerId
                + '","deliveryDateFrom":"' + _.deliverFrom + '","deliveryDateTo":"' + _.deliverTo + '","client":"' + _.client + '","salesperson_id":"' + _.salesperson_id + '","doc":"' + doc_type + '","backlink":"'+_.backlink+'","customer_name":"'+_.customer_name+'","customer_code":"'+_.customer_code+'","case_name":"'+_.case_name+'","status_name":"'+_.status_name+'","title_name":"'+_.title_name+'","isCustomerView":"'+_.isCustomerView+'","ship_via":"'+_.ship_via+'","wh_code":"'+_.wh_code+'"}}');

            jQuery.ajax({
                type: "POST",
                url: "./route.php",
                data: "data=" + data + "&action=getOrderWithView",
                success: function (msg) {
                    debugger
                    $('#ordersDiv').empty();
                    msg = isJSONparsable(msg);
                    
                    if(msg){
                        document.getElementById('btnSearch').removeAttribute("disabled");
                        document.getElementById('btnClear').removeAttribute("disabled");
                        debugger;
                        let views = msg.data;

                        if(typeof views == "undefined"){
                            // cause views can be array as well with no data
                            views = "";
                        }

                        if ((views.length == 0) && btnSearchClicked){
                            // $("#ordersDiv").append(msg.query);
                            $("#ordersDiv").append("<p class='no_orders'>No orders available</p>");
                        }else if( views.length == 0 ){
                            $("#ordersDiv").append("<p class='no_orders'>No orders today</p>");
                        }else{
                            if(views instanceof Array){
                                fullOrders = views;
                                for(let i = 0, len = views.length; i < len; i++){
                                    let view = views[i];
                                    //$("#ordersDiv").append(msg.query);
                                    $("#ordersDiv").append(view);
                                    
                                    var el = views[i];
                                    // console.log(el.innerHTML.split('<')[0].replace('| C','').replace('&amp;',''));
                                    
                                    if(localStorage.getItem("transfer_order_scrollTo")){
                                        $("html, body").animate({ scrollTop: localStorage.getItem("transfer_order_scrollTo") });
                                    }

                                }
                            }
                        }
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.warn(xhr);
                    $("#ordersDiv").append("<p class='no_orders'>Technical error. Please contact support</p>");
                }
            });
        }

        function isJSONparsable(value){
            if (typeof(value) !== "string"){
                return false;
            }
            try{
                return JSON.parse(value);
            }catch (error){
                return false;
            }
        }

        function toggle_cust_selection() {
            $("#salespersonDropDwn").hide();
            $("#customerDropDwn").toggle();
            //document.getElementById("customerDropDwn").classList.toggle("show");

        }
        function toggle_sp_selection() {
            $("#customerDropDwn").hide();
            $("#salespersonDropDwn").toggle();
            //document.getElementById("salespersonDropDwn").classList.toggle("show");
        }

        filter_cust = function filter_cust() {
            var input, filter, ul, li, a, i;
            input   = document.getElementById("customerSeachInp");
            filter  = input.value.toUpperCase();
            div     = document.getElementById("customerDropDwn");
            a       = div.getElementsByTagName("a");
            for (i = 0, len = a.length; i < len; i++) {
                txtValue    = a[i].textContent || a[i].innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    a[i].style.display = "";
                } else {
                    a[i].style.display = "none";
                }
            }
        };

        filter_salesperson = function filter_salesperson() {
            var input, filter, ul, li, a, i;
            input   = document.getElementById("salespersonSeachInp");
            filter  = input.value.toUpperCase();
            div     = document.getElementById("salespersonDropDwn");
            a       = div.getElementsByTagName("a");
            for (i = 0, len = a.length; i < len; i++) {
                txtValue    = a[i].textContent || a[i].innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    a[i].style.display = "";
                } else {
                    a[i].style.display = "none";
                }
            }
        };

        function phpSessionExists(key){
            let temp='';

            if(key === '_odate_from'){
                temp= "<?php echo $_SESSION['_odate_from'] ?>";
                if (temp==='') {
                    <?php if($client == 'ideamaker'): ?>
                    var date = new Date();
                    temp = new Date(date.getFullYear(), date.getMonth(), 2).toJSON().slice(0,10);
                    <?php else: ?>
                    temp= new Date().toJSON().slice(0,10);
                    <?php endif; ?>

                }
            }
            if(key === '_odate_to'){
                temp= "<?php echo $_SESSION['_odate_to'] ?>";
                if (temp==='') {
                    <?php if($client == 'ideamaker'): ?>
                    var date = new Date();
                    temp = new Date(date.getFullYear(), date.getMonth() + 1, 1).toJSON().slice(0,10);
                    <?php else: ?>
                    temp= new Date().toJSON().slice(0,10);
                    <?php endif; ?>
                }
            }
            if(key === '_ddate_from'){
                temp= "<?php echo $_SESSION['_ddate_from'] ?>";
            }
            if(key === '_ddate_to'){
                temp= "<?php echo $_SESSION['_ddate_to'] ?>";
            }
            if(key === '_view_cancel'){
                temp= "<?php echo $_SESSION['_view_cancel'] ?>";
            }
            if(key === '_salesperson_id'){
                temp= "<?php echo $_SESSION['_salesperson_id'] ?>";
            }
            if(key === '_cust_id'){
                temp= "<?php echo $_SESSION['_cust_id'] ?>";
            }
            if(key === '_customer_name'){
                temp= "<?php echo $_SESSION['_customer_name'] ?>";
            }

            return temp;
        }
    });

</script>

<!-- <script type="text/javascript">
	$(document).ready(function() {
        $('.order_img').magnificPopup({
            delegate: 'a',
            type: 'image',
            closeOnContentClick: false,
            closeBtnInside: false,
            mainClass: 'mfp-with-zoom mfp-img-mobile',
            image: {
                verticalFit: true,
                titleSrc: function(item) {
                    return item.el.attr('title') + ' &middot; <a class="image-source-link" href="'+item.el.attr('data-source')+'" target="_blank">image source</a>';
                }
            },
            gallery: {
                enabled: true
            },
            zoom: {
                enabled: true,
                duration: 300, // don't foget to change the duration also in CSS
                opener: function(element) {
                    return element.find('img');
                }
            }
            
        });
    });
</script> -->
<style>

    #customerSeachInp {
        border-box: box-sizing;
        background-image: url('./images/search.png');
        background-position: 14px 12px;
        background-repeat: no-repeat;
        font-size: 16px;
        padding: 12px 15px 12px 35px;
        border: none;
        width: 100%;
        border-bottom: 1px solid #ddd;
    }

    #customerSeachInp:focus {outline: 3px solid #ddd;}

    #salespersonSeachInp {
        border-box: box-sizing;
        background-image: url('./images/search.png');
        background-position: 14px 12px;
        background-repeat: no-repeat;
        font-size: 16px;
        padding: 12px 15px 12px 35px;
        border: none;
        width: 100%;
        border-bottom: 1px solid #ddd;
    }

    #salespersonSeachInp:focus {outline: 3px solid #ddd;}

    #caseSelection:focus {outline: none;}

    #statusSelection Option {background-color: rgb(239, 239, 239);color:black;}

    #titleSelection Option {background-color: rgb(239, 239, 239);color:black;}

    #shipViaSelection Option {background-color: rgb(239, 239, 239);color:black;}

    .dropdown {
        position: relative;
        display: inline-block;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        background-color: #f6f6f6;
        min-width: 97%;
        max-width: 97%;
        overflow: auto;
        border: 1px solid #ddd;
        z-index: 1;
        height: 50%;
    }

    .dropdown-content a {
        color: black;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
    }

    .dropdown a:hover {background-color: #ddd;}

    .show {display: block;}


    ::placeholder { /* Chrome, Firefox, Opera, Safari 10.1+ */
        color: #147efb;
        opacity: 1; /* Firefox */
    }

    :-ms-input-placeholder { /* Internet Explorer 10-11 */
        color: #147efb;
    }

    ::-ms-input-placeholder { /* Microsoft Edge */
        color: #147efb;
    }
    body {
        font-family: 'Open Sans';
        font-size: 16px;
        line-height:1;
        user-select: none;
        -webkit-user-select: none;
        -ms-user-select: none;
        -webkit-touch-callout: none;
        -o-user-select: none;
        -moz-user-select: none;
        padding:5px;
        overflow: scroll;
    }
    input.button_add {
        line-height: 35px;
        height: 35px;
        padding-left: 25px;
        border: 1px solid #147efb;
        background: #fff url('./images/calendar.png') no-repeat 5px center;
        cursor: pointer;
        width:100%;
    }
    input.dropdown_add {
        height: 35px;
        padding-left: 25px;
        border: 1px solid #147efb;
        background: #fff url('./images/building.png') no-repeat 5px center;
        cursor: pointer;
        width:100%;
    }
    .text {
        color:#147efb;
        text-align:left;
    }
    .text_white {
        color:#fff;
        text-align:left;
    }
    .non_important_text {
        font-size: 14px;
    }
    .radius {
        border-radius:3px;
    }
    .divider {
        height:5px
    }
    .buttons {
        color:#e8ebef;
        border: 1px solid #147efb;
        background: #147efb;
        width: 100%;
        display: inline;
        text-align:center;
        padding: 5px;
        box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
        transition: 0.3s;
        z-index: 1;
        height:35px;
    }

    .title {
        color:#147efb;
        text-align:left;
        margin-block-start: 0.5em;
        margin-block-end: 0em;
        font-weight: bold;
    }
    .dates {
        text-align:left;
    }
    .message {
        color: grey;
        text-align:left;
        font-style: italic;
    }
    p {
        display: block;
        margin-block-start: 0.5em;
        margin-block-end: 0.5em;
        margin-inline-start: 0.5px;
        margin-inline-end: 0px;
        margin-top: 3px;
        padding-top: 3px;
        margin-bottom: 3px;
        padding-bottom: 3px;
    }
    

    .delivery_status {
        display: inline;
    }

    hr {
        display: block;
        unicode-bidi: isolate;
        margin-block-start: 0em;
        margin-block-end: 0em;
        margin-inline-start: auto;
        margin-inline-end: auto;
        margin: 3px;
        padding: 3px;
    }
    .delivery-buttons {
        margin-inline-end: 5px;
        color:#e8ebef;
        border: 1px solid #147efb;
        background: #147efb;
        width: 32%;
        display: inline;
        text-align:center;
        padding: 5px;
        box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
        transition: 0.3s;
    }
    .reject-button-st {
        margin-inline-end: 5px;
        color:#e8ebef;
        border: 1px solid rgb(255,85,45);
        background: rgb(255,85,45);
        width: 32%;
        display: inline;
        text-align:center;
        padding: 5px;
        box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
        transition: 0.3s;
    }
    .approve-button-st {
        margin-inline-end: 5px;
        color:#e8ebef;
        border: 1px solid #3CB371;
        background: #3CB371;
        width: 32%;
        display: inline;
        text-align:center;
        padding: 5px;
        box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
        transition: 0.3s;
    }
    .search_field {
        border: 1px solid #147efb;
        font-size: 100%;
        width:100%;
    }

    .customer_selection_button {
        box-sizing: border-box;
        -moz-box-sizing: border-box;
        -webkit-box-sizing: border-box;
        height: 35px;
        padding-left: 25px;
        border: 1px solid #147efb;
        background: #147efb url('./images/customer.png') no-repeat 5px center;
        cursor: pointer;
        width:100%;
        box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
        transition: 0.3s;
    }

    .red_button {
        color:#e8ebef;
        border: 1px solid rgb(255,85,45);
        width: 100%;
        display: inline;
        text-align:center;
        padding: 5px;
        box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
        transition: 0.3s;
        background: rgb(255,85,45);
        z-index: 1;
        height:35px;
    }
    .loading-customer-div {
        min-width:100%;max-width:100%;align-items:center;justify-content:center;margin:10px;
    }
    .no_orders {
        font-size:14px;
        color:grey;
        position: absolute;
        top: 65%;
        left: 50%;
        -moz-transform: translateX(-50%) translateY(-50%);
        -webkit-transform: translateX(-50%) translateY(-50%);
        transform: translateX(-50%) translateY(-50%);
    }

    .dropdown-div{
        margin-left: -5px;
        margin-right: -5px;
        box-sizing: border-box;
        -moz-box-sizing: border-box;
        -webkit-box-sizing: border-box;
        height: 30px;
        color:gray;
        font-weight: bold;
        font-size:14px;
        background-color: #f4f5f7;

    }

    .total-sales{

        box-sizing: border-box;
        -moz-box-sizing: border-box;
        -webkit-box-sizing: border-box;
        height: 30px;
        line-height: 30px;
        color:#147efb;
        font-weight: bold;
        font-size:14px;
        border: 1px solid #147efb;
        text-align:center;

    }
    .center-text{
        position: relative;
        float: left;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
    select{
        -moz-appearance: none;
        -webkit-appearance: none;
        appearance: none;
        height: 100%;
        width: 100%;
        padding: 5px;
        border: 1px solid #147efb;
        color:#fff;
        background: url('./images/dropdown.png') no-repeat right #147efb;
        background-position-x : 99%;
    }

    .check {
        display: flex;
        justify-content: center;
        align-items: center;
        width:120px;
    }

    .checkbox_rounded {
        position: relative;
        cursor: pointer;
        display: flex;
        justify-content: center;
        align-items: center;
        width: 25px;
        height: 25px;
    }

    .checkbox_rounded input {
        opacity: 1;
    }

    .checkbox_rounded .checkbox_hover {
        position: absolute;
        width: 100%;
        height: 100%;
        background: white;
        border-radius: 50%;
        border: 4px solid #d6d6d6;
        transition: all 0.5s;
    }

    .checkbox_rounded input:checked + .checkbox_hover {
        box-shadow: inset 0 0 0 8px #147efb;
        border: none;
    }

    * {
        box-sizing: border-box;
    }

    .column {
        float: left;
        width: 33.33%;
        padding-top: 10px;
        padding-bottom: 10px;
        height: 35px;
    }

    .row:after {
        content: "";
        display: table;
        clear: both;
    }
    .no-message {
        color:grey;
        border: 1px solid lightgrey;
        background: lightgrey;
        width: 32%;
        display: inline;
        text-align:center;
        padding: 5px;

    }
    .loader {
    border: 6px solid #f3f3f3;
    border-radius: 50%;
    border-top: 6px solid #147efb;
    width: 60px;
    height: 60px;
    -webkit-animation: spin 2s linear infinite; /* Safari */
    animation: spin 2s linear infinite;
    }

    .loader-customer {
        border: 6px solid #f3f3f3;
        border-radius: 50%;
        border-top: 6px solid #147efb;
        width: 20px;
        height: 20px;
        -webkit-animation: spin 2s linear infinite; /* Safari */
        animation: spin 2s linear infinite;
    }

    /* Safari */
    @-webkit-keyframes spin {
        0% { -webkit-transform: rotate(0deg); }
        100% { -webkit-transform: rotate(360deg); }
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .child_div{
        float:left;
        margin-right:5px;
        font-size:14px;
        color:grey
    }
    .load-wrapp {
    width:200px;
        height:20px;
        float:left;
    display: inline-block;
    }
    .line {
        display: inline-block;
        width: 7px;
        height: 7px;
        border-radius: 7px;
        background-color: #147efb;
    }

    .load-2 .line:nth-last-child(1) {animation: loadingB 1.5s 1s infinite;}
    .load-2 .line:nth-last-child(2) {animation: loadingB 1.5s .5s infinite;}
    .load-2 .line:nth-last-child(3) {animation: loadingB 1.5s 0s infinite;}

    /* Safari */
    @-webkit-keyframes loadingB {
            0% { -webkit-transform: rotate(0deg); }
            100% { -webkit-transform: rotate(360deg); }
        }
    @keyframes loadingB {
        0% {width: 7px;}
        50% {width: 15px;}
        100% {width: 7px;}
    }

    .order_img {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1; /* Sit on top */
    padding: 10px; /* Location of the box */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 80%; /* Full height */
    overflow-x: auto; /* Enable scroll if needed */
    background-color: rgb(0,0,0); /* Fallback color */
    background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
    flex-direction: row;
    }

    .close {
    color: black;
    float: right;
    font-size: 28px;
    font-weight: bold;
    }

    .img_button{
    color: #e8ebef;
    border: 1px solid #147efb;
    background: #147efb;
    width: 32%;
    display: inline-block;
    text-align: center;
    padding: 5px;
    border-radius: 3px;
    font-size: 14px;
    }

    .attachment {
        display:flex;
		text-align: center;
		padding: 0px 5px;
		border: 1px solid #ddd;
		max-width: 100%;
        max-height: 100%;
		margin: 2vw;
		border-radius: 3px;
        padding:5px;
	}
	.attachment img {
		max-width: 100%;
		max-height: 100%;
	}
</style>
</body>
</html>
