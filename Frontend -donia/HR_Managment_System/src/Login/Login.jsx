import React, { useState } from "react";
// import '../App.css'
import './Login.css'

function Login() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [errors, setErrors] = useState({});

  const validate = () => {
    const newErrors = {};
    if (!email) {
      newErrors.email = "Email is required";
    } else if (!/\S+@\S+\.\S+/.test(email)) {
      newErrors.email = "Email format is invalid";
    }

    if (!password) {
      newErrors.password = "Password is required";
    } else if (password.length < 6) {
      newErrors.password = "Password must be at least 6 characters";
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (validate()) {
      alert("Form submitted successfully!");
    }
  };

  return (
    <div className="login-container">
      <div className="logo-icon" />
      <i className="bi bi-building"></i>
      <h3> HR Management System</h3>
      <h3>Login to your account</h3>

      <form onSubmit={handleSubmit}>
        <label>Email Address</label>
        <input
          type="email"
          placeholder="Enter your email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
        />
        {errors.email && <span className="error-text">{errors.email}</span>}

        <div className="password-row">
          <label>Password</label>
          <a href="#">Reset Password</a>
        </div>
        <input
          type="password"
          placeholder="Enter your password"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
        />
        {errors.password && (
          <span className="error-text">{errors.password}</span>
        )}

        <div className="remember-row">
          <input type="checkbox" id="remember" />
          <label htmlFor="remember">Remember me</label>
        </div>

        <button type="submit" className="login-button">
          Login
        </button>

        <p className="register">
          Don't have an account? <a href="#">Register instead</a>
        </p>
      </form>

      <footer>© 2023 HR Management System. All rights reserved.</footer>
    </div>
  );
}

export default Login;
