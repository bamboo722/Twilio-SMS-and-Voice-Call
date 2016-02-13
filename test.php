<?php
//phpinfo();
@session_start();	
if (! isset($_COOKIE['cookie_login']) and !isset($_SESSION['user_login'])) {//session store admin name
    header("Location: index.php"); //login in AdminLogin.php
}

require_once("includes/Services/Twilio/Capability.php");		// Twilio Call

/* twilio */
$accountSid = $_SESSION['tw_account_sid'];
$authToken  = $_SESSION['tw_auth_token'];
$capability = new Services_Twilio_Capability($accountSid, $authToken);
$capability->allowClientOutgoing($_SESSION['tw_app_sid']);
$_SESSION['tw_token'] = $capability->generateToken();
?>
<!DOCTYPE HTML>
<html>
    <head>
        <title>Twilio Test</title>
        
        <!-- utf8 setting -->
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	    
	    <!-- Bootstrap -->
	    <meta name="viewport" content="width=device-width, initial-scale=1">
	    <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
      
        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js" ></script>
 		<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js" ></script>
		
		<!-- Twilio -->
		<script type="text/javascript" src="//static.twilio.com/libs/twiliojs/1.2/twilio.min.js"></script>
	  
    
		     
		<script type="text/javascript">
			var auto_interval;
			setInterval(getEmailStatus, 1000*60*2);
		
			/**
			* Twilio Call
			*/ 
			Twilio.Device.setup("<?php echo $_SESSION['tw_token']; ?>", {"debug":true});
		      
			Twilio.Device.ready(function (device) {
		        $("#log").text("Ready");
		    });

		    Twilio.Device.error(function (error) {
		        $("#log").text("Error: " + error.message);
		    });

			Twilio.Device.connect(function (conn) {
				$("#log").text("Successfully established call");
			});

			Twilio.Device.disconnect(function (conn) {
			      $("#log").text("Call ended");
			});
			
			function ClicktoCall(addr)
	        {
	        	console.log("ClicktoCall : " + addr);
	        	if (addr=="")
	        	{
					alert("Phone number is wrong!");
				}	        		
	        	else
	        	{	
	        		addr = "+1"+addr;	        		
	        		params = {"tocall": addr,"callerid":"<?php echo $_SESSION['tw_number']; ?>"}; 	        		
			  		Twilio.Device.connect(params);     		
			  		
			  		var str = document.getElementById("calls_div").innerHTML;
	            	console.log(str);
	            	var str_ary = str.split("/");
	            	var calls_made = parseInt(str_ary[0])+1;
	            	var calls_connected = parseInt(str_ary[1]);
					document.getElementById("calls_div").innerHTML = calls_made+"/"+calls_connected;	
						  
				}				
	        }              	        			
			function Hangup()
			{
				$("#dialog_phone").modal('hide');   	
				Twilio.Device.disconnectAll();
			}
			Twilio.Device.incoming(function (connection) {
			     if (confirm('Accept incoming call from ' + connection.parameters.From + '?')){
			         connection.accept();
			     } else {
			         connection.reject();
			  }
			});
     		/**-----------------------------**/
			
			         
           	$(document).ready(function () {
           				
				// single
				$('#characterLeft').text('160 characters left');
				$('#msg_body').keyup(function () {
			        var max = 160;
			        var len = $(this).val().length;
			        if (len >= max) {
			            $('#characterLeft').text('You have reached the limit');
			           
			            $('#btnSMSSubmit').addClass('disabled');            
			        } 
			        else {
			            var ch = max - len;
			            $('#characterLeft').text(ch + ' characters left');
			            $('#btnSMSSubmit').removeClass('disabled');
			           
			        }
			    });   
			    		
			
								
            });       		
       		
       		
       	           
	        /* send sms */
	        function ClicktoSMS(addr)
            {
            	console.log("ClicktoSMS");
            	
        		document.getElementById("phone_numbers").value = addr;
        		
            	$("#dialog_phone").modal('hide');
            	
            	document.getElementById("msg_body").innerHTML ='Hello, '+document.getElementById('p_fl_nm').value+'\n';
            	$('#dialog_sms').modal();	
				
			}
			
			/* SMS Preview */
			function previewSMS()
       		{
       			console.log("PreviewSMS");      			
			
				var data = {
					"sms_to":document.getElementById('phone_numbers').value,
					"sms_sal":document.getElementById('msg_sal').value,
					"sms_body":document.getElementById('msg_body').value						
				};
				console.log(data);
       			
			    $.ajax({
			        url: 'previewSMS.php',
			        data: data,
			        type:"POST",
					dataType : "json",
			        success: function ( res ) {
			        	console.log("Success");
			        	console.log(res);
			        	if (res.status == "Success")
			        	{
			        		if (res.sms_body != "")
			        		{
			        			console.log(res.sms_body);
								document.getElementById("sms_preview_div").innerHTML =res.sms_body;								
          						$('#dialog_preview_sms').modal();            					
							}							
						}							
			        }
			    });				   
			}
			
		
			  
       		function sendSMS(){
				var data = {
					"sms_to":document.getElementById('phone_numbers').value,
					"sms_sal":document.getElementById('msg_sal').value,
					"sms_body":document.getElementById('msg_body').value						
				};
				console.log(data);
				$.ajax({
					type:"POST",
					dataType : "json",
					url : "sendsms.php",
					data : data,
					success : function(res){
						console.log(res);
						document.getElementById("sms_div").innerHTML = res.sms_sent+"/"+res.sms_recv;	
						if (res.status == 'Success')
							alert("Send SMS Success!");								
						else
							alert("Send SMS Error");
						
					},
					error:function(res)
					{
						console.log("fail");
						console.log(res);
					}
				});
				$("#dialog_sms").modal('hide');		
				$('#dialog_preview_sms').modal('hide');
		   }
		   
       	
			
		
			   
			/* click to call, sms */
			function ClicktoPhone(ph_num,p_fl_nm)
			{
				console.log("ClicktoPhone");
				document.getElementById("p_fl_nm").value = p_fl_nm;
        		document.getElementById("dialog_phone_number").innerHTML = ph_num;
        		$("#dialog_phone").modal();
			}
			
						
			
        </script>
       
    </head>
    <body >
    	
    	        
		<!-- click to phone (call,sms) modal dialog -->
        <div id="dialog_phone" class="modal fade" title="" style="display:none;">
        	<div class="modal-dialog modal-md">
				<div class="modal-content">
					<div class="modal-header">
				        <button type="button" class="close" data-dismiss="modal">&times;</button>
				        <h2 class="modal-title"><center><label id='dialog_phone_number'></label></center></h2>				    	
			      	</div>
			      	<div class="modal-body">
			      		<div class="row">
			      			<div class="col-xs-4">
			      				<button type="submit" style="font-size:26px" onclick="javascript:ClicktoCall(document.getElementById('dialog_phone_number').innerHTML)" class="btn btn-default btn-primary btn-md btn-block"><!--<span class="glyphicon glyphicon-earphone"></span>-->&nbsp;Call</button>
			      			</div>
			      			<div class="col-xs-4">
			      				<button type="submit" style="font-size:26px"  onclick="javascript:Hangup();" class="btn btn-default btn-primary btn-md btn-block">Hangup</button>
			      			</div>
			      			<div class="col-xs-4">
			      				<button type="submit" style="font-size:26px" onclick="javascript:ClicktoSMS(document.getElementById('dialog_phone_number').innerHTML)" class="btn btn-default btn-primary btn-md btn-block"><!--<span class="glyphicon glyphicon-envelope"></span>-->&nbsp;Text</button>
			      			</div>
			      		</div>			
			      		<div class="row">		      			
  							<h3 style="padding-left:5px;margin:0px;"><label id='log'>Loading...</label></h3>
  						</div>	      
			      	</div>			     
				</div>
			</div>
		</div>
		
      	<!-- sms send modal dialog -->
        <div id="dialog_sms" class="modal fade" title="Send SMS" style="z-index:1000000002;display:none;">
        	<div class="modal-dialog modal-sm">
				<div class="modal-content">
					<div class="modal-header">
				        <button type="button" class="close" data-dismiss="modal">&times;</button>
				        <h4 class="modal-title">Send SMS</h4>
			      	</div>
			      	<div class="modal-body">
			      		<div class="form-group">
				        	<label for="phone_numbers"><span class="glyphicon glyphicon-user"></span> Enter numbers</label>
				        	<input type="text" name="phone_numbers" id="phone_numbers" 
						      	    value="<?php
								  	 	  $p_ph1="";
								  	 	  $p_ph2="";
								  	 	  
								  	 	  if (isset($_POST['p_ph1'])) {
				                             $p_ph1 =$_POST['p_ph1'];
				                          } else if (isset($recb['p_ph1'])) {
				                              $p_ph1 =$recb['p_ph1'];
				                          } 
				                           if (isset($_POST['p_ph2'])) {
				                             $p_ph2 =$_POST['p_ph2'];
				                          } else if (isset($recb['p_ph2'])) {
				                              $p_ph2 =$recb['p_ph2'];
				                          } 
				                          
								  	 	  $default_email_to="";
								  	 	  
								  	 	  if ($p_ph1 != '') 
								  	 	  {
										  	$default_email_to =$p_ph1;	
										  	if ($p_ph2 !='') 
										  	{
												$default_email_to .= ";".$p_ph2;	
											}
										  }else if ($p_ph2 !='') 
										  {
											$default_email_to = $p_ph2;	
										  }
								  	 	  echo  $default_email_to; 
								  	 	  ?>"
								  class="form-control" placeholder="Enter Phone Numbers"/>				            
				        </div>
				        <div class="form-group">
				        	<label for="user_email_attach_file_name">Salutation</label>
				        	<input type="text" name="msg_sal" id="msg_sal"   value="Hello" class="form-control"/>
				        </div>
				        <div class="form-group">
				          	<label for="msg_body">Message</label>
				           	<textarea id="msg_body" name="msg_body" rows="7"  maxlength="160" class="form-control"></textarea>	
				           		<span class="help-block"><p id="characterLeft" class="help-block ">You have reached the limit</p></span>     			    
				        </div>			
			      	</div>
					<div class="modal-footer">
						<div class="col-xs-6">
			      			<button type="button" id="btnSMSSubmit"   name="btnSMSSubmit" onclick="javascript:sendSMS()" class="btn btn-default btn-success btn-block"><span class="glyphicon glyphicon-off"></span>&nbsp;Send</button>			        	 
			      		</div>
			      		<div class="col-xs-6">
			      			<button type="button" onclick="javascript:previewSMS()" class="btn btn-default btn-success btn-block"><span class="glyphicon glyphicon-eye-open"></span>&nbsp;Preview</button>			        	 
			      		
			      	</div>	
				</div>
			</div>
			</div>		 
		</div>
		<!-- sms preview dialog -->
        <div id="dialog_preview_sms" class="modal fade" style="z-index:1000000003;display:none;" title="" >
        	<div class="modal-dialog modal-md">
				<div class="modal-content">
					<div class="modal-header">
				        <button type="button" class="close" data-dismiss="modal">&times;</button>
				        <h4 class="modal-title">Preview SMS</h4>				        
			      	</div>
			      	<div class="modal-body" style="overflow:auto" id="sms_preview_div">
			      		
			      	</div>	
			      	<div class="modal-footer">
			      		<center>			      			
			      			<div class="col-xs-offset-4 col-xs-4">
			      				<button type="submit" onclick="javascript:sendSMS()" class="btn btn-default btn-success btn-block"><span class="glyphicon glyphicon-off"></span>&nbsp;Send</button>
			      			</div>			      							      		
			      		</center>			      		
			      	</div>				     
				</div>
			</div>
		</div>
		
	
    </body>
  
</html>

		