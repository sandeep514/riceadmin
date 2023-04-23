<div style="font-family: Helvetica,Arial,sans-serif;min-width:1000px;overflow:auto;line-height:2">
  <div style="margin:50px auto;width:70%;padding:20px 0">
    <div style="border-bottom:1px solid #eee">
      <a href="" style="font-size:1.4em;color: #00466a;text-decoration:none;font-weight:600">SNTC Rice Live Pricing App</a>
    </div>
    <p style="font-size:1.1em">Hi,</p>
    <p>Dear Team, You got a new bid. </p>

    <ul>
      <li>Name : {{ $user->name }}</li>
      <li>Email : {{ $user->email }}</li>
      <li>Mobile : {{ $user->mobile }}</li>
    </ul>


    <ul>
      <li>Buy Query No : {{ $bid.id }}</li>
      <li>Quality Name : {{ $bid.qualityName }}</li>
      <li>Quantity : {{ $bid.quantity }}</li>
      <li>Remarks : {{ $bid.remarks }}</li>
    </ul>

    <p style="font-size:0.9em;">Regards,<br />SNJ Tradelink Pvt. Ltd.</p>
    <hr style="border:none;border-top:1px solid #eee" />
    <div style="float:right;padding:8px 0;color:#aaa;font-size:0.8em;line-height:1;font-weight:300">
		<p>SNJ Tradelink Pvt. Ltd.</p>
		<p>5593/94, 3rd Floor Lahori Gate,</p>
		<p>Naya Bazar, Delhi-110006</p>
		<p>India</p>
    </div>
  </div>
</div>