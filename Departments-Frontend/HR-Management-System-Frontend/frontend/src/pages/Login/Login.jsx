import React, { useState } from "react";
import { useNavigate } from "react-router-dom";
import api from "../../api"; // Ensure this points to your Axios setup
import "./Login.css";


const Login = () => {
  const navigate = useNavigate();
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [errors, setErrors] = useState({});

  const handleSubmit = async (e) => {
    e.preventDefault();
    const newErrors = {};

    // Validation rules
    if (!email.trim()) {
      newErrors.email = "Please enter a valid email address.";
    }
    if (!password.trim()) {
      newErrors.password = "Please enter your password.";
    }

    if (Object.keys(newErrors).length > 0) {
      setErrors(newErrors);
      return;
    }

    try {
      const response = await api.post("hr/login", { email, password });
      localStorage.setItem("token", response.data.token);
      navigate("/holidays");
    } catch (error) {
      setErrors({
        email: "Invalid email or password.",
        password: "Invalid email or password.",
      });
    }
  };

  return (
    <div className="login-page">
      <div className="login-container glass-card">
        <div className="logo-icon"></div>
        <h3>Welcome Back</h3>
        <form onSubmit={handleSubmit} noValidate>
          <div>
            <label>Email Address</label>
            <input
              type="email"
              placeholder="Enter your email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
            />
            {errors.email && <div className="error-text">{errors.email}</div>}
          </div>

          <div>
            <label>Password</label>
            <input
              type="password"
              placeholder="Enter your password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
            />
            {errors.password && (
              <div className="error-text">{errors.password}</div>
            )}
          </div>

          <div className="remember-row">
            <input type="checkbox" id="remember" />
            <label htmlFor="remember">Remember me</label>
          </div>

          <div className="password-row">
            <a href="#">Forgot password?</a>
          </div>

          <button className="login-button" type="submit">
            Login
          </button>
        </form>

        <div className="register">
          Don’t have an account? <a href="#">Register here</a>
        </div>

        <footer>© 2025 HR Management System</footer>
      </div>
    </div>
  );
};

export default Login;
