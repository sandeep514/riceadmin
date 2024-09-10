<div style="font-family: Helvetica,Arial,sans-serif;min-width:1000px;overflow:auto;line-height:2">
  <div style="margin:50px auto;width:70%;padding:20px 0">
    <div style="border-bottom:1px solid #eee">
      <a href="" style="font-size:1.4em;color: #00466a;text-decoration:none;font-weight:600">SNTC Rice Live Pricing App</a>
    </div>
    <p style="font-size:1.1em">Hi,</p>
    <p>Dear Team, You got a new buy query. </p>

    <ul>
      <li> Buyer: {{ $username }} </li>
      <li> Email: {{ $email }} </li>
      <li> mobile: {{ $mobile }} </li>
      <li> Country: {{ $country }} </li>
    </ul>
    <br>
    <br>
    <ul>
      <li>Quality Name: {{ $query['qualityName'] }}</li>
      <li>Port Name: {{ $query['portName'] }}</li>
      <li>Packing: {{ $query['getPackingType']['bag_size'] }} {{ $query['getPackingType']['bag_type'] }}</li>
      <li>Quantity: {{ $query['quantity'] }}</li>
      <li>Remarks: {{ $query['remarks'] }}</li>
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