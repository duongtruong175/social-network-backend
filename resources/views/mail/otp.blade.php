@component('mail::message')

<div style="font-size:14px;line-height:140%;padding:25px 35px">
    <h1 style="font-size:20px;font-weight:bold;line-height:1.3;margin:0 0 15px 0">Social Network</h1>
    <p style="margin:0;padding:0">
        {{ $content }}
    </p>
</div>

<div style="font-size:14px;line-height:140%;padding:25px 35px;padding-top:0;text-align:center">
    <div style="font-weight:bold;padding-bottom:15px">
        Mã xác minh
    </div>
    <div style="color:#000;font-size:36px;font-weight:bold;padding-bottom:15px">
        {{ $code }}
    </div>
    <div>
        (Mã này có giá trị trong 10 phút)
    </div>
</div>

@endcomponent