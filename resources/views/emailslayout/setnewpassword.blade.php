<h2>PracticePA</h2>
<b>Dear {{$data['first_name']}}</b><br><br>
<p>Reset.<br><br>
<a href="{{$data['UrlPath']}}reset_password/{{$data['ForgetCode']}}/{{base64_encode($data['Email'])}}">Click here</a> for activation or copy and paste the link below to your browser. <br>
    {{$data['UrlPath']}}reset_password/{{$data['ForgetCode']}}/{{base64_encode($data['Email'])}}
</p>

 <br><br>
<p>Please click here for FAQs and should you have any further enquiries, drop us an email on hello@practicepa.co.uk. <br>
Kind Regards,<br>
The PracticePA Team
</p>
