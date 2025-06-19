import React from "react";
import { useNavigate, useParams } from "react-router-dom";
import api from "../../api";
import "./Department.css";

export default function DepartmentForm({ isEdit }) {
  const navigate = useNavigate();
  const { id } = useParams();
  const [formData, setFormData] = React.useState({ name: "", description: "" });
  const [error, setError] = React.useState("");
  const [success, setSuccess] = React.useState("");
  const [loading, setLoading] = React.useState(isEdit);

  React.useEffect(() => {
    if (isEdit && id) {
      api.get(`/departments/${id}`)
        .then((res) => {
          const { name, description } = res.data;
          setFormData({ name, description: description || "" });
          setLoading(false);
        })
        .catch(() => {
          setError("Failed to load department.");
          setLoading(false);
        });
    }
  }, [id, isEdit]);

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    const request = isEdit
      ? api.put(`/departments/${id}`, formData)
      : api.post("/departments", formData);

    request
      .then(() => {
        setSuccess(`Department ${isEdit ? "updated" : "created"} successfully!`);
        setError("");
        setTimeout(() => {
          navigate("/departments");
        }, 1500);
      })
      .catch((err) => {
        setError(err.response?.data?.message || "An error occurred.");
        setSuccess("");
      });
  };

  if (loading) return <div className="text-center fs-4">Loading...</div>;

  return (
    <div className="page-wrapper py-5 px-3">
      <div className="mb-4 component-area"></div>

      <div className="glass-form-card">
        <div className="mb-4">
          <h2 className="text-primary">
            {isEdit ? "✏️ Edit Department" : "🏢 Create New Department"}
          </h2>
          {success && <div className="alert alert-success">{success}</div>}
        </div>

        {error && <div className="alert alert-danger">{error}</div>}

        <form onSubmit={handleSubmit}>
          <div className="mb-3 text-start">
            <label className="form-label fw-semibold">Department Name</label>
            <input
              type="text"
              name="name"
              value={formData.name}
              onChange={handleChange}
              className="form-control"
              required
            />
          </div>

          <div className="mb-4 text-start">
            <label className="form-label fw-semibold">Description</label>
            <textarea
              name="description"
              value={formData.description}
              onChange={handleChange}
              className="form-control"
              rows="3"
            ></textarea>
          </div>

          <button type="submit" className="btn btn-primary w-100">
            {isEdit ? "Update Department" : "Create Department"}
          </button>
        </form>
      </div>
    </div>
  );
}
