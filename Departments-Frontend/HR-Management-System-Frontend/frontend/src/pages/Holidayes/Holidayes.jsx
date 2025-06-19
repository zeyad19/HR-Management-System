import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import api from "../../api";
import "./Holiday.css";

const Holidayes = () => {
  const [holidays, setHolidays] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showConfirm, setShowConfirm] = useState(false);
  const [deleteId, setDeleteId] = useState(null);
  const navigate = useNavigate();

  useEffect(() => {
    api.get("/holidays")
      .then(res => {
        setHolidays(res.data.data);
        setLoading(false);
      })
      .catch(err => {
        console.error(err);
        setLoading(false);
      });
  }, []);

  const openConfirm = (id) => {
    setDeleteId(id);
    setShowConfirm(true);
  };

  const handleDelete = () => {
    api.delete(`/holidays/${deleteId}`)
      .then(() => {
        setHolidays(holidays.filter(h => h.id !== deleteId));
        setShowConfirm(false);
      })
      .catch(err => {
        console.error(err);
        setShowConfirm(false);
      });
  };

  if (loading) return <div className="text-center fs-4 text-primary">Loading...</div>;

  return (
    <div className="holiday-page-wrapper">
  <div className="container">
    <div className="d-flex justify-content-between align-items-center mb-4">
      <h2 className="holiday-section-title">📅 Holidays List</h2>
      <button className="btn btn-success" onClick={() => navigate("/create")}>
        + Add Holiday
      </button>
    </div>

    {holidays.length === 0 ? (
      <div className="holiday-alert-message holiday-glass-card">No holidays found.</div>
    ) : (
      <div className="row g-4">
        {holidays.map((holiday, index) => (
          <div key={holiday.id} className="col-md-4">
            <div className="holiday-glass-card p-4">
              <h5 className="holiday-card-title">{holiday.name}</h5>
              <p className="holiday-date-short">{new Date(holiday.date).toLocaleDateString()}</p>
              <p className="holiday-date-full">{new Date(holiday.date).toLocaleDateString(undefined, {
                weekday: "long", year: "numeric", month: "long", day: "numeric"
              })}</p>
              <div className="d-flex justify-content-center gap-3 mt-3">
                <button onClick={() => navigate(`/holidays/${holiday.id}`)} className="btn btn-outline-primary btn-sm">
                  Edit
                </button>
                <button onClick={() => openConfirm(holiday.id)} className="btn btn-outline-danger btn-sm">
                  Delete
                </button>
              </div>
            </div>
          </div>
        ))}
      </div>
    )}

    {showConfirm && (
      <div className="holiday-modal-overlay">
        <div className="holiday-modal-card p-4">
          <h5>Confirm Delete</h5>
          <p>Are you sure you want to delete this holiday?</p>
          <div className="d-flex justify-content-end gap-3 mt-4">
            <button className="btn btn-outline-light" onClick={() => setShowConfirm(false)}>Cancel</button>
            <button className="btn btn-danger" onClick={handleDelete}>Delete</button>
          </div>
        </div>
      </div>
    )}
  </div>
</div>

  );
};

export default Holidayes;
