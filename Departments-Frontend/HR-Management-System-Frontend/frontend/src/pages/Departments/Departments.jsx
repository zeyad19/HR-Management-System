import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import api from "../../api";
import "./Department.css";

const Departments = () => {
  const [departments, setDepartments] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState("");
  const [sortBy, setSortBy] = useState("name");
  const [sortOrder, setSortOrder] = useState("asc");
  const navigate = useNavigate();

  useEffect(() => {
    api.get("/departments")
      .then((res) => {
        setDepartments(res.data.data || res.data);
        setLoading(false);
      })
      .catch((err) => {
        console.error(err);
        setLoading(false);
      });
  }, []);

  const handleSort = (value) => {
    const [field, order] = value.split("-");
    setSortBy(field);
    setSortOrder(order);
  };

  const filteredDepartments = departments
    .filter((dept) =>
      dept.name.toLowerCase().includes(searchTerm.toLowerCase())
    )
    .sort((a, b) => {
      const fieldA = a[sortBy];
      const fieldB = b[sortBy];

      if (typeof fieldA === "string") {
        return sortOrder === "asc"
          ? fieldA.localeCompare(fieldB)
          : fieldB.localeCompare(fieldA);
      }

      return sortOrder === "asc" ? fieldA - fieldB : fieldB - fieldA;
    });

  if (loading) return <div className="text-center fs-4 text-primary">Loading...</div>;

  return (
    <div className="department-page-wrapper">
      <div className="container">
        <div className="d-flex justify-content-between align-items-center mb-4">
          <h2 className="department-section-title">🏢 Departments</h2>
          <button className="btn btn-success" onClick={() => navigate("/departments/create")}>
            + Add Department
          </button>
        </div>

        {/* Search + Sort UI */}
        <div className="row mb-4">
          <div className="col-md-8">
            <input
              type="text"
              className="form-control department-form-control"
              placeholder="🔍 Search departments..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
            />
          </div>
          <div className="col-md-4">
            <select
              className="form-select department-form-control"
              value={`${sortBy}-${sortOrder}`}
              onChange={(e) => handleSort(e.target.value)}
            >
              <option value="name-asc">Sort by Name ↑</option>
              <option value="name-desc">Sort by Name ↓</option>
              <option value="employees_count-asc">Employees ↑</option>
              <option value="employees_count-desc">Employees ↓</option>
            </select>
          </div>
        </div>

        {/* Department Cards */}
        {filteredDepartments.length === 0 ? (
          <div className="department-alert-message department-glass-card">No departments found.</div>
        ) : (
          <div className="row g-4">
            {filteredDepartments.map((department) => (
              <div key={department.id} className="col-md-4">
                <div className="department-glass-card p-4">
                  <h5 className="department-card-title">{department.name}</h5>
                  <p className="department-date-short">Code: DEP-{String(department.id).padStart(3, '0')}</p>
                  <p className="department-date-full">Employees: {department.employees_count || 0}</p>
                  <div className="d-flex justify-content-center gap-3 mt-3">
                    <button onClick={() => navigate(`/departments/${department.id}`)} className="btn btn-outline-primary btn-sm">
                      Edit
                    </button>
                    <button className="btn btn-outline-danger btn-sm" onClick={() => alert("Delete here")}>
                      Delete
                    </button>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
};

export default Departments;
