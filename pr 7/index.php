
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration | Practical 7</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
        }

        .wrapper {
            width: 100%;
            max-width: 850px;
            display: grid;
            grid-template-columns: 40% 60%;
            background: white;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0,0,0,0.25);
        }

        .left {
            background: linear-gradient(160deg, #4f46e5, #7c3aed);
            color: white;
            padding: 45px 35px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .logo {
            font-size: 45px;
            margin-bottom: 20px;
        }

        .left h1 {
            font-size: 32px;
            margin-bottom: 15px;
        }

        .left p {
            line-height: 1.7;
            color: #e0e7ff;
        }

        .features {
            margin-top: 30px;
        }

        .feature {
            margin: 15px 0;
            font-size: 15px;
        }

        .right {
            padding: 40px;
        }

        .right h2 {
            color: #222;
            font-size: 28px;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #777;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 7px;
            color: #333;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 13px 15px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 15px;
            outline: none;
            transition: 0.3s;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.12);
        }

        textarea {
            resize: vertical;
        }

        .submit-btn {
            width: 100%;
            border: none;
            padding: 14px;
            border-radius: 10px;
            background: linear-gradient(90deg, #4f46e5, #7c3aed);
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(79,70,229,0.3);
        }

        .records {
            text-align: center;
            margin-top: 18px;
        }

        .records a {
            color: #4f46e5;
            text-decoration: none;
            font-weight: bold;
        }

        @media (max-width: 700px) {
            .wrapper {
                grid-template-columns: 1fr;
            }

            .left {
                padding: 30px;
                text-align: center;
            }

            .features {
                display: none;
            }
        }
    </style>
</head>

<body>

<div class="wrapper">

    <div class="left">

        <div class="logo">🎓</div>

        <h1>StudentHub</h1>

        <p>
            Welcome to our student registration portal.
            Enter your details and submit the form to securely
            store your information.
        </p>

        <div class="features">
            <div class="feature">✓ Server-side validation</div>
            <div class="feature">✓ Secure input sanitization</div>
            <div class="feature">✓ CSV file storage</div>
            <div class="feature">✓ Easy record management</div>
        </div>

    </div>


    <div class="right">

        <h2>Create Registration</h2>

        <p class="subtitle">
            Fill in your details below
        </p>

        <form action="process.php" method="POST">

            <div class="form-group">
                <label for="name"> Full Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    placeholder="Enter your full name"
                    required
                >
            </div>

            <div class="form-group">
                <label for="email"> Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="example@gmail.com"
                    required
                >
            </div>

            <div class="form-group">
                <label for="phone"> Phone Number</label>
                <input
                    type="text"
                    id="phone"
                    name="phone"
                    placeholder="10 digit phone number"
                    required
                >
            </div>

            <div class="form-group">
                <label for="gender">⚧ Gender</label>

                <select id="gender" name="gender" required>

                    <option value="">Select Gender</option>

                    <option value="Male">
                        Male
                    </option>

                    <option value="Female">
                        Female
                    </option>

                    <option value="Other">
                        Other
                    </option>

                </select>
            </div>

            <div class="form-group">
                <label for="message">💬 Message</label>

                <textarea
                    id="message"
                    name="message"
                    rows="4"
                    placeholder="Enter your message..."
                ></textarea>
            </div>

            <button type="submit" class="submit-btn">
                Register Now →
            </button>

        </form>

        <div class="records">
            <a href="records.php">
                 View Stored Records
            </a>
        </div>

    </div>

</div>

</body>
</html>

