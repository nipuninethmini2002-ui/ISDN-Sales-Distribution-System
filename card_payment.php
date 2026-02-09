<!DOCTYPE html>
<html>
<head>
    <title>Card Payment - ISDN</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #4CAF50, #2e8b57);
            margin: 0;
            padding: 0;
        }

        .box {
            width: 420px;
            margin: 90px auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }

        h3 {
            text-align: center;
            color: #2e8b57;
            margin-bottom: 20px;
        }

        label {
            font-size: 14px;
            color: #333;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 6px 0 14px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        input:focus {
            outline: none;
            border-color: #4CAF50;
        }

        .btn {
            padding: 12px;
            width: 100%;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            cursor: pointer;
            margin-top: 10px;
        }

        .done {
            background: #4CAF50;
            color: #fff;
        }

        .done:hover {
            background: #45a049;
        }

        .cancel {
            background: #e74c3c;
            color: #fff;
        }

        .cancel:hover {
            background: #c0392b;
        }

        .error {
            color: red;
            font-size: 13px;
            margin-bottom: 10px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="box">
    <h3>Card Payment</h3>

    <div class="error" id="error"></div>

    <label>Card Number</label>
    <input type="text" id="cardNumber" placeholder="16-digit card number" maxlength="16" oninput="this.value=this.value.replace(/\D/g,'')">

    <label>Card Holder Name</label>
    <input type="text" id="cardName" placeholder="Name on card">

    <label>Expiry Date (MM/YY)</label>
    <input type="text" id="expiry" placeholder="MM/YY" maxlength="5" oninput="formatExpiry(this)">

    <label>CVV</label>
    <input type="password" id="cvv" placeholder="3-digit CVV" maxlength="3" oninput="this.value=this.value.replace(/\D/g,'')">

    <button class="btn done" onclick="donePayment()">Done</button>
    <button class="btn cancel" onclick="cancelPayment()">Cancel</button>
</div>

<script>
function formatExpiry(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length > 2) {
        value = value.substring(0,2) + '/' + value.substring(2,4);
    }
    input.value = value;
}

function donePayment() {
    const cardNumber = document.getElementById("cardNumber").value.trim();
    const cardName   = document.getElementById("cardName").value.trim();
    const expiry     = document.getElementById("expiry").value.trim();
    const cvv        = document.getElementById("cvv").value.trim();
    const errorBox   = document.getElementById("error");

    errorBox.innerHTML = "";

    if (!/^\d{16}$/.test(cardNumber)) {
        errorBox.innerHTML = "Card number must be 16 digits.";
        return;
    }

    if (cardName.length < 3) {
        errorBox.innerHTML = "Please enter card holder name.";
        return;
    }

    if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(expiry)) {
        errorBox.innerHTML = "Expiry date must be in MM/YY format.";
        return;
    }

    if (!/^\d{3}$/.test(cvv)) {
        errorBox.innerHTML = "CVV must be 3 digits.";
        return;
    }

    window.location.href = "place_order.php?card_done=1";
}

function cancelPayment() {
    window.location.href = "place_order.php";
}
</script>

</body>
</html>
