import React, { useState } from "react";
import "./RegistrationForm.css";
import ProfileImageUpload from "./ProfileImageUpload";

const RegistrationForm = () => {
  const [formData, setFormData] = useState({
    fullName: "",
    email: "",
    password: "",
    confirmPassword: "",
    profileImage: null,
    agree: false,
  });

  const [errors, setErrors] = useState({});

  const handleChange = (e) => {
    const { name, value, type, files, checked } = e.target;
    setFormData({
      ...formData,
      [name]:
        type === "file" ? files[0] : type === "checkbox" ? checked : value,
    });
  };

  const validateForm = () => {
    const newErrors = {};
    if (!formData.fullName) newErrors.fullName = "Full name is required";
    if (!formData.email) newErrors.email = "Email is required";
    else if (!/\S+@\S+\.\S+/.test(formData.email))
      newErrors.email = "Invalid email address";
    if (!formData.password || formData.password.length < 6)
      newErrors.password = "Password must be at least 6 characters";
    if (formData.password !== formData.confirmPassword)
      newErrors.confirmPassword = "Passwords do not match";
    if (!formData.profileImage)
      newErrors.profileImage = "Profile image is required";
    if (!formData.agree) newErrors.agree = "You must agree to the terms";
    return newErrors;
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    const validationErrors = validateForm();
    if (Object.keys(validationErrors).length === 0) {
      console.log("Form submitted:", formData);
    } else {
      setErrors(validationErrors);
    }
  };

  return (
    <div className="register-container">
      <div className="logo-icon" />
      <h2>Add HR</h2>
      <p className="subtitle">Join the HR Management System</p>

      <form onSubmit={handleSubmit} className="registration-form">
        <h2>Add HR</h2>
        <p>Join the HR Management System</p>
        
        <label>Profile Image</label>
        <ProfileImageUpload onChange={handleChange} />
        {errors.profileImage && (
          <span className="error-text">{errors.profileImage}</span>
        )}

        <label>Full Name</label>
        <input
          type="text"
          name="fullName"
          placeholder="Enter your full name"
          value={formData.fullName}
          onChange={handleChange}
        />
        {errors.fullName && (
          <span className="error-text">{errors.fullName}</span>
        )}

        <label>Email Address</label>
        <input
          type="email"
          name="email"
          placeholder="Enter your email address"
          value={formData.email}
          onChange={handleChange}
        />
        {errors.email && <span className="error-text">{errors.email}</span>}

        <label>Password</label>
        <input
          type="password"
          name="password"
          placeholder="Create a strong password"
          value={formData.password}
          onChange={handleChange}
        />
        {errors.password && (
          <span className="error-text">{errors.password}</span>
        )}

        <label>Confirm Password</label>
        <input
          type="password"
          name="confirmPassword"
          placeholder="Confirm your password"
          value={formData.confirmPassword}
          onChange={handleChange}
        />
        {errors.confirmPassword && (
          <span className="error-text">{errors.confirmPassword}</span>
        )}

        <div className="checkbox-row">
          <input
            type="checkbox"
            name="agree"
            id="agree"
            checked={formData.agree}
            onChange={handleChange}
          />
          <label htmlFor="agree">
            I agree to the <a href="#">Terms of Service</a> —{" "}
            <a href="#">Privacy Policy</a>
          </label>
        </div>
        {errors.agree && <span className="error-text">{errors.agree}</span>}

        <button type="submit" className="register-button">
          Add Account
        </button>

        <p className="login-link">
          Already have an account? <a href="#">Log in</a>
        </p>
      </form>
    </div>
  );
};

export default RegistrationForm;
