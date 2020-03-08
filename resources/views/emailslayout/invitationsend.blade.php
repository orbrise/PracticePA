<h2>Bizpad</h2>
<b>Dear {{$data['first_name']}}</b><br><br>
<p><b>{{ucfirst($data['company_name'])}}</b> has been sent you an invitation for joing Bizpad<br><br>
    <a href="{{$data['UrlPath']}}sign_up/{{$data['invitation_code']}}">Click here</a> for joining or copy and paste the link below to your browser. <br>
    {{$data['UrlPath']}}sign_up/{{$data['invitation_code']}}
</p>

<br><br>
<p>Please click here for FAQs and should you have any further enquiries, drop us an email on hello@practicepa.co.uk. <br>
    Kind Regards,<br>
    The PracticePA Team
</p>
