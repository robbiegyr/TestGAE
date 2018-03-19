<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>HW6</title>
<style type="text/css">
    table tr,th {
        background-color:#FFFFFF;
    }
    form input,select {
        margin-top: 2px;
        margin-bottom: 2px;
    }

        .dropbtn {
            cursor: pointer;
            transition-duration: 1s;
            margin: 0;
        }

        .dropdown {
            position: relative;
            display: inline-block;
            transition-duration: 1s;
        }

        .dropdown-content {
            position: absolute;
            background-color: #f9f9f9;
            min-width: 140px;
            z-index: 999;
            transition-duration: 1s;
        }

        .dropdown-content a {
            color: black;
            padding: 10px 10px;
            text-decoration: none;
            display: block;
            transition-duration:0.3s;
            cursor: pointer;
        }

        .dropdown-content a:hover {background-color: #dcdcdc;transition-duration: 0.3s;}

        .dropdown:hover .dropdown-content {

            display: block;
            transition-duration:0.3s;
        }

        .dropdown:hover .dropbtn {
            transition-duration:0.3s;
        }

        .dropbtn:hover {
            color: gray;
        }

        a{
            text-decoration: none;
            color: #000000;
        }

        a:hover {
            color: #191919;
        }

    </style>

</head>

<body>




<div id="form_sec" style="border:#CCC 3px solid; background-color: #fafafa; margin: 20px auto; padding:0 8px 20px; width: 600px; font-family:'Times New Roman'; ">
    <form name="search" action="2.php" method="GET" >
        <p style="text-align:center; font-size:34px; margin:0; padding:0;"><i>Travel and Entertainment Search</i></p>
        <hr/>
        <strong>Keyword</strong>
        <input type="text" id="keyword" name="key" value="" required="required" />
        <i hidden="hidden" id="fetch_location">   Fetching current location...</i>
        <br/>
        <strong>Category</strong>
        <select id="selt" style="margin-left:0px;" name="selt">
            <option value ="default">default</option>
            <option value ="cafe" >cafe</option>
            <option value ="bakery" >bakery</option>
            <option value ="restaurant" >restaurant</option>
            <option value ="beauty_salon" >beauty salon</option>
            <option value ="casino" >casino</option>
            <option value ="movie_theater" >movie theater</option>
            <option value ="lodging" >lodging</option>
            <option value ="airport" >airport</option>
            <option value ="train_station" >train station</option>
            <option value ="subway_station" >subway station</option>
            <option value ="bus_station" >bus station</option>
        </select>
        <br/>
        <div style="height:26px;">
         <span id="loc_id" style=" margin:0; padding:0;">
             <strong>Distance (miles)</strong><input id="dist" placeholder="10" onkeyup="this.value=this.value.replace(/[^0-9.]+/,'');" type="text" name="dis" style="margin-left:4px; width: 128px;"  value=""/>
             <strong> from </strong>
             <input id="here_radio" type="radio" name="select_location" checked value="here" onclick="setHere()">Here
             <br/>
             <input id="loc_radio" type="radio" style="margin-left:291px;" name="select_location" onclick="setOther()" value="other">
             <input id="manuLoc" type="text" name="loc" disabled  placeholder="location"  value=""/>
             <input type="text" id="location" name="defLoc" hidden="hidden" value=""/>
         </span>
        </div>
        <div style="margin-top: 30px">
            <input id="submit_btn" type="submit" name="submit" value="Search" disabled="disabled" style="margin-left:63px;" />
            <input type="button" name="clear" value="Clear" onclick="clearAll(this.form)">
        </div>
    </form>
</div>





<table style="word-break:break-all;word-wrap:break-word" id="mainTable" hidden="hidden" align="center" width="1200px" border="0" bgcolor="#dddddd">
    <tbody id="tbody">
        <tr bgcolor="#fafafa" >
            <th width="100px">Category</th><th>Name</th><th>Address</th>
        </tr>
    </tbody>
</table>

<?php

    //---------fuction definations---------------------------------------------------------------
    function get_cord($loc){
        $google_token = 'AIzaSyDW41RRDIJyfYzGtoP_JH6ogKCEUOLZv-c';
        $g_url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.$loc.'&key='.$google_token;
        $streamContext = stream_context_create([
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false
            ]
        ]);
        $g_response = file_get_contents($g_url,false,$streamContext);
        if(!$g_response){
            $res['lat']=null;
            $res['lng']=null;
        }
        else{
            $g_json = json_decode($g_response,true);
            if($g_json['status']=="ZERO_RESULTS"){
                $res['lat']=null;
                $res['lng']=null;
            }
            else{
                $res['lat'] = $g_json['results'][0]['geometry']['location']['lat'];
                $res['lng'] = $g_json['results'][0]['geometry']['location']['lng'];
            }
        }

        return $res;
    }

    if(isset($_GET["submit"])){


        $key_word  = urlencode($_GET["key"]);
        //$empty_location = false;

        if(isset($_GET["select_location"]) && ($_GET["select_location"] == "here")){
            $location = $_GET["defLoc"];
            $lat = substr($location,0,intval(strrpos($location,",")));
            $lon = substr($location,intval(strrpos($location,","))+1, strlen($location));
        }
        else{
            $loc  = urlencode($_GET["loc"]);
            $res = get_cord($loc);
            $lat = $res['lat'];
            $lon = $res['lng'];
        }

        if(isset($_GET["dis"]) && ($_GET["dis"] != "")){
            $dis  = (string) (1609 * intval($_GET["dis"]));
        }
        else {
            $dis = 1609 * 10 ."" ;
        }


        if($lat==null || $lon==null){
            echo '<p align="center" style="background-color:#f0f0f0; width:750px; margin: 20px auto; border: solid 2px #dddddd; font-family:\'Times New Roman\';" >Address is invalid</p>';
            return;
        }
        else {

            $request_url = 'https://maps.googleapis.com/maps/api/place/nearbysearch/json?location='.$lat.','.$lon.'&radius='.$dis.'&keyword='.$key_word;

            if($_GET["selt"] == "default"){
            }
            else {
                $request_url = $request_url.'&type='.$_GET["selt"];
            }

            $request_url = $request_url.'&key=AIzaSyBa2q0avJs0GxVoeTF_Q-nWf22M0DDJvas';

            $streamContext = stream_context_create([
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false
                ]
            ]);
            //$request_url=str_replace('amp;','',$request_url);
            $response = file_get_contents($request_url,false, $streamContext);
            $result = json_decode($response, true);
        }

        //--------------No records found---------------------------------------------
        if( !isset($result['results']) || count($result['results']) == 0){
            //echo '<p align="center" style="background-color:#f0f0f0; width:750px; margin: 20px auto; border: solid 2px #dddddd; font-family:\'Times New Roman\';" >No Records has been found</p>';
            $big_arr = array();
            $big_arr[] = "no_record";

            if(isset($_GET["select_location"]) && ($_GET["select_location"] == "here")){
                $big_arr[] = array($key_word, $_GET["selt"],$_GET["dis"], $_GET["select_location"], $_GET["defLoc"]);
            }
            else{
                $big_arr[] = array($key_word, $_GET["selt"],$_GET["dis"], $_GET["select_location"], $_GET["loc"]);
            }

            $big_json = json_encode($big_arr);
            echo '<script> var big_json = '.$big_json.'</script>';
        }
        else{

            $count_json = count($result['results']);

            $icon_arr = array();
            $name_arr = array();
            $address_arr = array();
            $map_arr = array();
            $street_view_arr = array();
            $placeid_arr = array();
            $place_lat = array();
            $place_lon = array();


            for ($i = 0; $i < $count_json; $i++) {

                $dt = $result['results'][$i];
                $icon_arr[] = $dt['icon'];
                $name_arr[] = $dt['name'];
                $address_arr[] = $dt['vicinity'];
                $place_lat[] = $dt['geometry']['location']['lat'];
                $place_lon[] = $dt['geometry']['location']['lng'];
                //$img = file_get_contents('https://maps.googleapis.com/maps/api/staticmap?format=jpg&center='.urlencode($dt['vicinity']).'&zoom=13&size=500x300&maptype=roadmap&markers=color:red%7Clabel:S%7C'.urlencode($dt['vicinity']).'&key=AIzaSyBTTB_yyJa-dSi4Ktyrc80w0HM5szIHNAQ');
                //file_put_contents($i.'map.jpg',$img);

                if(isset($dt['photos'][0]['html_attributions'][0])){
                    $map_arr[] = $dt['photos'][0]['html_attributions'][0];
                }
                else{
                    $map_arr[] = "Invalid";
                }

                //$street_view_img =urlencode("https://maps.googleapis.com/maps/api/streetview?size=600x400&location=".urlencode($dt['vicinity'])."&key=AIzaSyBa2q0avJs0GxVoeTF_Q-nWf22M0DDJvas&heading=180");
                //$street_view_arr[] = $street_view_img;
                $placeid_arr[] = $dt['place_id'];

            }

            $big_arr = array();
            $big_arr[]= $icon_arr;
            $big_arr[] = $name_arr;
            $big_arr[] = $address_arr;
            $big_arr[] = $map_arr;
            $big_arr[] =  $street_view_arr;
            $big_arr[] =  $placeid_arr;
            $big_arr[] =  $place_lat;
            $big_arr[] =  $place_lon;
            $big_arr[] =  $lat;
            $big_arr[] =  $lon;

            if(isset($_GET["select_location"]) && ($_GET["select_location"] == "here")){
                $big_arr[] = array($key_word, $_GET["selt"],$_GET["dis"], $_GET["select_location"], $_GET["defLoc"]);
            }
            else{
                $big_arr[] = array($key_word, $_GET["selt"],$_GET["dis"], $_GET["select_location"], $_GET["loc"]);
            }



            $big_json = json_encode($big_arr);

            echo '<script> var big_json = '.$big_json.'</script>';

        }
    }

    //------------------------------------------------------------------------------------------------------------------

    if(isset($_GET["action"])){
        $act = $_GET["action"];

        if($act=="getdetail"){

            $place_id = $_GET["id"];
            $place_name = urldecode($_GET["place_name"]);
            $request_url = 'https://maps.googleapis.com/maps/api/place/details/json?placeid='.$place_id.'&key=AIzaSyBa2q0avJs0GxVoeTF_Q-nWf22M0DDJvas';

            $streamContext = stream_context_create([
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false
                ]
            ]);
            $response = file_get_contents($request_url,false,$streamContext);
            $result = json_decode($response, true);

            $review_photo_arr = array();
            $review_name_arr = array();
            $review_content_arr = array();
            $photo_url_arr = array();


            //display reviews....
            $reviews = array();

            if(isset($result['result']['reviews'])){
                $count_review = count($result['result']['reviews']);
            }
            else {
                $count_review = 0;
            }

            if ($count_review > 5) $count_review = 5;
            for($i = 0; $i<$count_review; $i++){
                $reviews[] = $result['result']['reviews'][$i]['text'];

            }

            for ($i = 0; $i <  $count_review; $i++) {
                $review_content_arr[] = $result['result']['reviews'][$i]['text'];
                $review_name_arr[] = $result['result']['reviews'][$i]['author_name'];

                if(isset($result['result']['reviews'][$i]['profile_photo_url'])){
                    $review_photo_arr[] = $result['result']['reviews'][$i]['profile_photo_url'];
                } else {
                    $review_photo_arr[] = "no_profile";
                }
            }

            $pics_id = array();
            $pics_url = array();

            if(isset($result['result']['photos'])){
                $count_pic = count($result['result']['photos']);
            }
            else {
                $count_pic = 0;
            }

            if ($count_pic > 5) $count_pic = 5;
            for($i = 0; $i<$count_pic; $i++){

                $photo_url_arr[] = $result['result']['photos'][$i]['photo_reference'];
                $pics_id[] = $result['result']['photos'][$i]['photo_reference'];

                $streamContext = stream_context_create([
                    'ssl' => [
                        'verify_peer'      => false,
                        'verify_peer_name' => false
                    ]
                ]);
                $img = file_get_contents('https://maps.googleapis.com/maps/api/place/photo?maxwidth=800&photoreference='.$result['result']['photos'][$i]['photo_reference'].'&key=AIzaSyBa2q0avJs0GxVoeTF_Q-nWf22M0DDJvas',false,$streamContext);
                file_put_contents($i.'img.myImg',$img);
                //echo '<img src="'.$i.'img">';


            }

            $detail_arr = array();
            $detail_arr[] = $review_photo_arr;
            $detail_arr[] = $review_name_arr;
            $detail_arr[] = $review_content_arr;
            $detail_arr[] = $photo_url_arr;
            $detail_arr[] = $place_name;

            if(isset($_GET["select_location"]) && ($_GET["select_location"] == "here")){
                $detail_arr[] = array($_GET["key"], $_GET["selt"],$_GET["dis"], $_GET["select_location"]);
            }
            else{
                $detail_arr[] = array($_GET["key"], $_GET["selt"],$_GET["dis"], $_GET["select_location"], $_GET["loc"]);
            }

            $detail_json = json_encode($detail_arr);

            echo '<script> var detail_json = '.$detail_json.'</script>';

        }

    }
?>

<script type="text/javascript">

    //alert(document.location.protocol );

    function getLocationUsingHttp(){

        //http for current location
        function createXHR() {
                var xhr = null;
                if (window.XMLHttpRequest) {
                    xhr = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
                    xhr = new ActiveXObject("Microsoft.XMLHTTP");
                }
                return xhr;
            }

        var url = "http://ip-api.com/json";

        xmlhttp=null;
        if (window.XMLHttpRequest){
            xmlhttp=new XMLHttpRequest();
        }
        else if (window.ActiveXObject) {// code for IE6, IE5
            xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
        }
        if (xmlhttp!=null) {
            xmlhttp.open("GET",url,false);
            xmlhttp.send(null);
            var jsonObj = JSON.parse(xmlhttp.responseText);
            latt = jsonObj.lat;
            lonn = jsonObj.lon;
            document.search.defLoc.value = latt+","+lonn;
            document.getElementById('fetch_location').innerHTML = "   Got current location!";
            document.getElementById("submit_btn").removeAttribute("disabled");
        }
        else {
            alert("Your browser does not support XMLHTTP.");
        }

    }



    //https for current location
    /*function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showPosition);
        } else {
            document.getElementById('fetch_location').innerHTML = "   Please use https protocl";
        }
    }*/

    function setHere(){
        document.getElementById('manuLoc').value = "";
        document.getElementById('manuLoc').setAttribute("disabled","disabled");
        document.getElementById('manuLoc').removeAttribute("required");
    }

    function setOther(){
        document.getElementById('manuLoc').value = "";
        document.getElementById('manuLoc').removeAttribute("disabled");
        document.getElementById('manuLoc').setAttribute("required","required");
        document.getElementById("submit_btn").removeAttribute("disabled");
    }
    function prevent(e){
        alert("@");
        e.preventDefault();
        return false;
    }

    /*function showPosition(position) {
        latt = position.coords.latitude;
        lonn = position.coords.longitude;
        document.search.defLoc.value = latt+","+lonn;
        document.getElementById('fetch_location').innerHTML = "   Got current location!";
        document.getElementById("submit_btn").removeAttribute("disabled");
    }*/

    function show_pic(url){
        //------------------------------------------------------
        var win2 = window.open("","HW6");
        var body_element=win2.document.getElementsByTagName('body')[0];
        var img=win2.document.createElement("img");
        img.src=url;
        body_element.appendChild(img);
        body_element.setAttribute("style","text-align: center");
    }

    function show_map(type,destination,index){

        var directionsService = new google.maps.DirectionsService;
        var directionsDisplay = new google.maps.DirectionsRenderer;
        var map = new google.maps.Map(document.getElementById('map' + index), {
            zoom: 7,
            center: {lat: search_lat, lng: search_lon}
        });
        directionsDisplay.setMap(map);
        calculateAndDisplayRoute(directionsService, directionsDisplay,type,destination);


    }

    function calculateAndDisplayRoute(directionsService, directionsDisplay,type,destination) {
        directionsService.route({
            origin: search_lat + "," + search_lon,
            destination: destination ,
            travelMode: type
        }, function(response, status) {
            if (status === 'OK') {
                directionsDisplay.setDirections(response);
            } else {
                window.alert('Directions request failed due to ' + status);
            }
        });
    }


    function clearAll(form){
        newNode=document.getElementById('form_sec').cloneNode(true);
        document.getElementsByTagName('body')[0].innerHTML = "";
        document.getElementsByTagName('body')[0].appendChild(newNode);
        document.search.key.value = "";
        document.search.loc.value = "";//latt+","+lonn;
        document.search.dis.value = "";
        document.search.selt.value = "default";

        big_json = null;
        document.getElementById('manuLoc').setAttribute("disabled","disbaled");
        document.getElementsByName("select_location")[0].checked = "checked";
        //document.search.defLoc.value = latt+","+lonn;

    }

    function show_alb(){
        if(document.getElementById('alb_tb').getAttribute('hidden') === ""){
            document.getElementById('alb_tb').removeAttribute('hidden');
            document.getElementById('photo_arrow').setAttribute('src','arrow_up.png');
            document.getElementById('photo_text').innerHTML = "click to hide photos";
            if(document.getElementById('pst_tb').getAttribute('hidden') === null) {
                document.getElementById('pst_tb').hidden="hidden";
                document.getElementById('review_arrow').setAttribute('src','arrow_down.png');
                document.getElementById('review_text').innerHTML = "click to show reviews";
            }
            else {}
        }
        else{
            document.getElementById('alb_tb').hidden="hidden";
            document.getElementById('photo_arrow').setAttribute('src','arrow_down.png');
            document.getElementById('photo_text').innerHTML = "click to show photos";
            //document.getElementById('review_arrow').setAttribute('src','arrow_up');
        }

    }

    function show_pst(){
        if(document.getElementById('pst_tb').getAttribute('hidden') === ""){
            document.getElementById('pst_tb').removeAttribute('hidden');
            document.getElementById('review_arrow').setAttribute('src','arrow_up.png');
            document.getElementById('review_text').innerHTML = "click to hide reviews";
            if(document.getElementById('alb_tb').getAttribute('hidden') === null){
                document.getElementById('alb_tb').hidden="hidden";
                document.getElementById('photo_arrow').setAttribute('src','arrow_down.png');
                document.getElementById('photo_text').innerHTML = "click to show photos";
            }
            else{}
        }
        else{
            document.getElementById('pst_tb').hidden="hidden";
            document.getElementById('review_arrow').setAttribute('src','arrow_down.png');
            document.getElementById('review_text').innerHTML = "click to show reviews";
            //document.getElementById('photo_arrow').setAttribute('src','arrow_up');
        }

    }


    function loadXMLDoc(url) {
        var xmlhttp = new XMLHttpRequest();

        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == XMLHttpRequest.DONE) {   // XMLHttpRequest.DONE == 4
                if (xmlhttp.status == 200) {
                    alert(xmlhttp.responseText);
                }
                else if (xmlhttp.status == 400) {
                    alert('There was an error 400');
                }
                else {
                    alert('something else other than 200 was returned');
                }
            }
        };

        xmlhttp.open("GET", url, true);
        xmlhttp.send();
    }

    function open_map(i) {
        if(document.getElementById('dropdiv' + i).style.display === "none"){
            document.getElementById('dropdiv' + i).style.display = "block";
            var uluru = {lat:big_json[6][i] , lng: big_json[7][i]};
            var map = new google.maps.Map(document.getElementById('map' + i), {
                zoom: 13,
                center: uluru
            });
            var marker = new google.maps.Marker({
                position: uluru,
                map: map
            });
        }
        else {
            document.getElementById('dropdiv' + i).style.display = "none";
        }
    }

    function set_form_data(n) {
        document.getElementById("keyword").value = big_json[n][0];
        document.getElementById("selt").value = big_json[n][1];
        document.getElementById("dist").value = big_json[n][2];
        if(big_json[n][3] === "other"){
            document.getElementById("loc_radio").checked = true;
            document.getElementById("manuLoc").removeAttribute("disabled");
            document.getElementById("manuLoc").value = big_json[n][4];
        }
        else {
            document.getElementById("here_radio").checked = true;
            document.getElementById("manuLoc").disabled = "disabled";
        }
    }

    function set_detail_form_data() {
        document.getElementById("keyword").value = detail_json[5][0];
        document.getElementById("selt").value = detail_json[5][1];
        document.getElementById("dist").value = detail_json[5][2];
        if(detail_json[5][3] === "other"){
            document.getElementById("loc_radio").checked = true;
            //document.getElementById("loc_text").disabled = false;
            document.getElementById("manuLoc").value = detail_json[5][4];
        }
        else {
            document.getElementById("here_radio").checked = true;
            document.getElementById("manuLoc").disabled = "disabled";
        }
    }

    getLocationUsingHttp();


    try {
            if(big_json != null){


                if(big_json[0] === "no_record"){
                    set_form_data(1);

                    var div=document.createElement("div");
                    div.style.width = "100%";
                    div.setAttribute("align","center");
                    div.innerHTML = "<p align='center' style='background-color:#f0f0f0; width:750px; margin: 20px auto; border: solid 2px #dddddd;' >No Records has been found</p>";
                    document.body.appendChild(div);


                    //document.getElementById('mainTable').hidden = '';
                }
                else{
                    set_form_data(10);

                    search_lat = big_json[8];
                    search_lon = big_json[9];

                    for(var i = 0; i < big_json[0].length; i++){
                        var tr=document.createElement("tr");

                        //icon
                        var icon = document.createElement("td");
                        var  im = document.createElement("img");
                        im.setAttribute("src", big_json[0][i]);
                        im.setAttribute("width","60px");
                        im.setAttribute("height","40px");
                        icon.appendChild(im);
                        tr.appendChild(icon);

                        //name
                        namee = document.createElement("td");
                        a_name = document.createElement("a");
                        var temp;
                        if(document.getElementsByName("select_location")[0].checked == true){
                            temp = "here";
                        }
                        else {
                            temp = "other";
                        }
                        a_name.setAttribute("href","2.php?key=" + document.search.key.value + "&selt="+ document.search.selt.value +"&loc="+ document.search.loc.value +"&dis="+ document.search.dis.value +"&action=getdetail&id=" + big_json[5][i] + "&place_name=" + big_json[1][i] + "&select_location=" + temp);
                        a_name.innerHTML = "&nbsp&nbsp&nbsp&nbsp" + big_json[1][i];
                        namee.appendChild(a_name);
                        tr.appendChild(namee);

                        //address
                        addr = document.createElement("td");
                        addr.innerHTML =
                            "<div>" +
                            "<p onclick='open_map(\""+ i + "\")' class='dropbtn'>&nbsp&nbsp&nbsp&nbsp"+ big_json[2][i] +"</p>" +
                            "<div id='dropdiv" + i +"' class='dropdown-content' style='margin-left: 20px; display: none '>" +
                            "<div style='position: absolute; z-index: 100;background-color: #f0f0f0'>" +
                            "<a onclick='show_map(\"WALKING\",\""+ big_json[6][i]+","+ big_json[7][i]+"\",\""+ i +"\")'>Walk there</a>" +
                            "<a onclick='show_map(\"BICYCLING\",\""+ big_json[6][i]+","+ big_json[7][i]+"\",\""+ i +"\")'>Bike there</a>" +
                            "<a onclick='show_map(\"DRIVING\",\""+ big_json[6][i]+","+ big_json[7][i]+"\",\""+ i +"\")'>Drive there</a>" +
                            "</div>" +
                            "<div style='height: 300px; width:400px;' id='map" + i + "'></div>" +
                            "</div>"+
                            "</div> ";

                        tr.appendChild(addr);

                        document.getElementById('tbody').appendChild(tr);
                        document.getElementById('mainTable').hidden = '';

                    }
                }
            }

    }
    catch(err){

    }

    try{
        if(detail_json != null){

            set_detail_form_data();

            var div = document.createElement("div");

            //review
            var post_text = "";

            post_text += '<div align="center" ><strong>' + detail_json[4] + '</strong></div>';

            post_text += '</br>';


            post_text += '<div align="center" width:750px; style="margin: 20px 0 5px 0; "><a id="review_text" href="javascript:show_pst()">click to show reviews</a></div>'
                    + '<div align="center" width:750px; style="margin:0 0 5px 0; "> <a href="javascript:show_pst()"><img id="review_arrow" src="arrow_down.png" style="width: 30px;"/></a></div>'
                    + '<table id="pst_tb" width="630px" align="center" hidden="" border="0" bgcolor="#dddddd">';

            if(detail_json[0].length == 0){
                post_text+= '<tr>';
                post_text+= '<th>No Reviews Found</th>';
                post_text+= '</tr>';
            }


            for(var i = 0; i < detail_json[0].length; i++){
                post_text += '<tr>';

                if(detail_json[0][i] == "no_profile"){
                    post_text += '<th>'+detail_json[1][i]+'</th>';
                } else {
                    post_text += '<th><img style="max-width: 30px" src="'+detail_json[0][i]+'" /> '+detail_json[1][i]+'</th>';
                }

                post_text += '</tr>';
                post_text += '<tr><td>'+detail_json[2][i]+'</td></tr>';
            }

            post_text += '</table>';

            //photos
            var photo_text = "";
            photo_text += '<div align="center" style="margin: 20px 0 5px 0; " width:750px; ><a id="photo_text" href="javascript:show_alb()">click to show photos</a></div>';
            photo_text += '<div align="center" width:750px; style="margin:0 0 5px 0; "><a href="javascript:show_alb()"><img id="photo_arrow" src="arrow_down.png" style="width: 30px;" /></a></div>';
            photo_text += '<table id="alb_tb" width="630px" align="center" hidden="" border="0" bgcolor="#dddddd">';

            if(detail_json[3].length == 0){
                photo_text += '<tr>';
                photo_text += '<th>No Photos Found</th>';
                photo_text += '</tr>';
            }

            for (var i = 0; i <  detail_json[3].length; i++) {
                photo_text += '<tr>';

                photo_text += '<th style="padding:20px"><a target="_blank" href="'+i+'img.myImg"><img width="590px"  src="'+ i +'img.myImg" /></a></th>';

                photo_text += '</tr>';
            }

            photo_text += '</table>';
            var innerText = post_text + photo_text;
            div.innerHTML = innerText;
            document.getElementsByTagName('body')[0].appendChild(div);

        }
    }
    catch(err){
    }


</script>
<script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBGfe6kQA6FMroRvrprVcwmut248xIIUn0">
</script>
<div style="height: 350px"></div>
</body>
</html>