import React from "react";
import { useNavigate, useParams } from "react-router-dom";
import api from "../../api";
import "./Holiday.css";

export default function HolidayForm({ isEdit }) {
  const navigate = useNavigate();
  const { id } = useParams();
  const [formData, setFormData] = React.useState({ name: "", date: "" });
  const [error, setError] = React.useState("");
  const [success, setSuccess] = React.useState("");
  const [loading, setLoading] = React.useState(isEdit);

  React.useEffect(() => {
    if (isEdit && id) {
      api.get(`/holidays/${id}`)
        .then((res) => {
          const { name, date } = res.data.data;
          setFormData({ name, date: date.split("T")[0] });
          setLoading(false);
        })
        .catch(() => {
          setError("Failed to load holiday.");
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
      ? api.put(`/holidays/${id}`, formData)
      : api.post("/holidays", formData);

    request
      .then(() => {
        setSuccess(`Holiday ${isEdit ? "updated" : "created"} successfully!`);
        setError("");
        setTimeout(() => {
          navigate("/holidays");
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
        {isEdit ? "✏️ Edit Holiday" : "🎉 Create New Holiday"}
      </h2>
      {success && <div className="alert alert-success">{success}</div>}
    </div>

    {error && <div className="alert alert-danger">{error}</div>}

    <form onSubmit={handleSubmit}>
      <div className="mb-3 text-start">
        <label className="form-label fw-semibold">Holiday Name</label>
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
        <label className="form-label fw-semibold">Date</label>
        <input
          type="date"
          name="date"
          value={formData.date}
          onChange={handleChange}
          className="form-control"
          required
        />
      </div>

      <button type="submit" className="btn btn-primary w-100">
        {isEdit ? "Update Holiday" : "Create Holiday"}
      </button>
    </form>
  </div>
</div>

  );
}
