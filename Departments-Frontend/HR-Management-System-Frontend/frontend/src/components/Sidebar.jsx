import React, { useState, useEffect } from "react";
import { NavLink, Outlet } from "react-router-dom";
import {
  FaCalendarAlt,
  FaPlus,
  FaHome,
  FaUsers,
  FaMoneyBill,
  FaChevronDown,
  FaChevronUp,
  FaBuilding,
  FaCog,
  FaSignOutAlt,
} from "react-icons/fa";
import api from "../api";
import "./Sidebar.css";

export default function Sidebar() {
  const [openHolidays, setOpenHolidays] = useState(false);
  const [hr, setHr] = useState(null); // حالة تخزين بيانات الـ HR

  const linkStyle = ({ isActive }) =>
    isActive ? "nav-link full-button active" : "nav-link full-button";

  const handleLogout = () => {
    localStorage.removeItem("token");
    window.location.href = "/login";
  };

  // تحميل بيانات HR عند فتح الصفحة
  useEffect(() => {
    api
      .get("/user")
      .then((res) => setHr(res.data))
      .catch((err) => console.error("Failed to fetch HR data", err));
  }, []);

  return (
    <div className="d-flex">
      {/* Sidebar */}
      <aside className="sidebar">
        <div>
          <h4>HR System</h4>

          <div className="sidebar-menu">
            <NavLink to="/" className={linkStyle}>
              <FaHome />
              Home
            </NavLink>

            {/* Holidays Dropdown */}
            <div>
              <button
                onClick={() => setOpenHolidays(!openHolidays)}
                className="nav-link full-button"
              >
                <FaCalendarAlt />
                Holidays
                {openHolidays ? <FaChevronUp /> : <FaChevronDown />}
              </button>

              {openHolidays && (
                <div className="dropdown">
                  <NavLink to="/holidays" className={linkStyle}>
                    <FaCalendarAlt />
                    View Holidays
                  </NavLink>
                  <NavLink to="/create" className={linkStyle}>
                    <FaPlus />
                    Create Holiday
                  </NavLink>
                </div>
              )}
            </div>

            <NavLink to="/employees" className={linkStyle}>
              <FaUsers />
              Employees
            </NavLink>

            <NavLink to="/payroll" className={linkStyle}>
              <FaMoneyBill />
              Payroll
            </NavLink>

            <NavLink to="/departments" className={linkStyle}>
              <FaBuilding />
              Departments
            </NavLink>

            <NavLink to="/settings" className={linkStyle}>
              <FaCog />
              Settings
            </NavLink>

            <button onClick={handleLogout} className="nav-link full-button">
              <FaSignOutAlt />
              Logout
            </button>
          </div>
        </div>

        {/* User Info */}
{hr && (
  <div className="sidebar-user text-center mb-4">
    <img
      src={
        hr.profile_picture
          ? `http://localhost:8000/storage/${hr.profile_picture}`
          : "https://via.placeholder.com/100"
      }
      alt="HR Profile"
      className="rounded-circle border border-white"
      style={{
        width: "100px",
        height: "100px",
        objectFit: "cover",
        marginBottom: "12px",
        boxShadow: "0 0 8px rgba(255, 255, 255, 0.6)",
      }}
    />
    <p
      className="text-white fw-bold"
      style={{ fontSize: "1.2rem", marginBottom: 0, textShadow: "0 0 5px rgba(0,0,0,0.5)" }}
    >
      {hr.name}
    </p>
  </div>
)}



      </aside>

      {/* Main Content */}
      <main className="flex-grow-1 p-4 bg-light rounded-start">
        <Outlet />
      </main>
    </div>
  );
}
