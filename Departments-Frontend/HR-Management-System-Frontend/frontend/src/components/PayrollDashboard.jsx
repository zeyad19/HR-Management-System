
import React, { useEffect, useState, useCallback } from "react";
import axios from "axios";
import { Table, Button, Modal, Form, Spinner, Badge, Card, Alert } from "react-bootstrap";

const api = axios.create({
  baseURL: "http://localhost:8000/api",
  headers: {
    Authorization: "Bearer 4|qalOZ2rUjKCoAHohBWv8YZrKBVZEr7fFUJTjHOP7ce23f788", // Replace with valid token
  },
});

export default function PayrollTable() {
  const [employees, setEmployees] = useState([]);
  const [loading, setLoading] = useState(true);
  const [selectedEmployee, setSelectedEmployee] = useState(null);
  const [showModal, setShowModal] = useState(false);
  const [search, setSearch] = useState("");
  const [error, setError] = useState(null);

  const fetchData = useCallback(async () => {
    try {
      setLoading(true);
      setError(null);
      const { data } = await api.get("/payroll/all-employees-data");
      console.log("API Response:", data);
      setEmployees(data?.data || []);
    } catch (err) {
      console.error("Error fetching data:", err);
      setError("Failed to load employee data. Please check the server.");
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchData();
  }, [fetchData]);

  useEffect(() => {
    if (selectedEmployee) {
      console.log("Modal Employee Data:", JSON.stringify(selectedEmployee, null, 2));
    }
  }, [selectedEmployee]);

  const handleShow = (employee) => {
    setSelectedEmployee(employee);
    setShowModal(true);
  };

  const handleClose = () => {
    setShowModal(false);
    setSelectedEmployee(null);
  };

  const filteredEmployees = employees.filter(emp =>
    emp.full_name?.toLowerCase().includes(search.toLowerCase())
  );

  const getImageUrl = (emp) => {
    if (emp.profile_image_url) {
      return emp.profile_image_url;
    }
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(emp.full_name || "Unknown")}&background=random&rounded=true`;
  };

  const formatTime = (time) => {
    if (!time) return "Not Specified";
    return time.split(':').slice(0, 2).join(':'); // e.g., "09:00:00" -> "09:00"
  };

  const formatWeekendDays = (days) => {
    return Array.isArray(days) && days.length > 0 ? days.join(", ") : "Not Specified";
  };

  return (
    <div className="d-flex" style={{ minHeight: "100vh" }}>
      <style jsx>{`
        .glass-card {
          background: rgba(255, 255, 255, 0.1);
          backdrop-filter: blur(8px);
          border: 1px solid transparent;
          border-image: linear-gradient(45deg, #007bff, #ff6f61) 1;
          border-radius: 1.2rem;
          transition: transform 0.3s ease, box-shadow 0.3s ease;
          min-height: 100px;
        }
        .glass-card:hover {
          transform: translateY(-2px) scale(1.03);
          box-shadow: 0 10px 25px rgba(0, 123, 255, 0.2);
        }
        .modal-content {
          background: rgba(255, 255, 255, 0.9);
          backdrop-filter: blur(10px);
          border: none;
          border-radius: 1.5rem;
          overflow: hidden;
          max-height: 90vh; /* Enlarged height */
          max-width: 98vw; /* Enlarged width */
        }
        .modal-dialog {
          max-width: 98vw;
          margin: 1vh auto; /* Centered with minimal margin */
        }
        .modal-header {
          background: linear-gradient(45deg, #007bff, #6f42c1);
          border-bottom: none;
          padding: 1rem 1.5rem;
          color: white;
        }
        .modal-header .btn-close {
          background: rgba(255, 255, 255, 0.2);
          border-radius: 50%;
          transition: transform 0.3s ease;
        }
        .modal-header .btn-close:hover {
          transform: rotate(90deg);
        }
        .modal-body {
          background: #ffffff;
          padding: 2rem; /* Increased padding */
          overflow-y: auto;
        }
        .modal-footer {
          background: rgba(255, 255, 255, 0.1);
          backdrop-filter: blur(8px);
          border-top: none;
          padding: 1rem 1.5rem;
          border-radius: 0 0 1.5rem 1.5rem;
        }
        .footer-btn {
          background: linear-gradient(45deg, #007bff, #ff6f61);
          border: none;
          border-radius: 0.8rem;
          padding: 0.6rem 2rem;
          color: white;
          transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .footer-btn:hover {
          transform: scale(1.1);
          box-shadow: 0 5px 15px rgba(0, 123, 255, 0.4);
        }
        .navbar {
          border-top-right-radius: 1rem;
          border-bottom-right-radius: 1rem;
          transition: all 0.3s ease;
        }
        .navbar-link {
          display: block;
          padding: 0.5rem 1rem;
          border-radius: 0.5rem;
          transition: background-color 0.2s ease, transform 0.2s ease;
        }
        .navbar-link:hover {
          background-color: rgba(255, 255, 255, 0.1);
          transform: translateX(5px);
        }
        .section-divider {
          border-top: 1px solid rgba(0, 123, 255, 0.2);
          margin: 1.5rem 0; /* Increased margin */
        }
        .slide-in {
          animation: slideIn 0.3s ease-out forwards;
          opacity: 0;
        }
        @keyframes slideIn {
          from { opacity: 0; transform: translateY(10px); }
          to { opacity: 1; transform: translateY(0); }
        }
        @keyframes modalPop {
          from { opacity: 0; transform: scale(0.95); }
          to { opacity: 1; transform: scale(1); }
        }
        .slide-in:nth-child(1) { animation-delay: 0.05s; }
        .slide-in:nth-child(2) { animation-delay: 0.1s; }
        .slide-in:nth-child(3) { animation-delay: 0.15s; }
        .slide-in:nth-child(4) { animation-delay: 0.2s; }
        .slide-in:nth-child(5) { animation-delay: 0.25s; }
        .slide-in:nth-child(6) { animation-delay: 0.3s; }
        .slide-in:nth-child(7) { animation-delay: 0.35s; }
      `}</style>

      <aside
        className="bg-primary text-white p-3 navbar"
        style={{
          width: "220px",
          boxShadow: "2px 0 10px rgba(0,0,0,0.1)",
          position: "sticky",
          top: 0,
          height: "100vh",
        }}
      >
        <h4 className="mb-4 text-center">Dashboard</h4>
        <nav>
          <ul className="list-unstyled">
            <li className="mb-2">
              <a href="#" className="text-white text-decoration-none navbar-link">Home</a>
            </li>
            <li className="mb-2">
              <a href="#" className="text-white text-decoration-none navbar-link">Employees</a>
            </li>
            <li className="mb-2">
              <a href="#" className="text-white text-decoration-none navbar-link">Payroll</a>
            </li>
          </ul>
        </nav>
      </aside>

      <main className="flex-grow-1 p-4 bg-light">
        <div className="d-flex justify-content-between align-items-center mb-4">
          <h2 className="fw-bold text-primary">💼 Payroll Dashboard</h2>
          <Form.Control
            type="text"
            placeholder="🔎 Search by name..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            style={{ maxWidth: "300px", borderColor: "#007bff", borderRadius: "0.5rem" }}
          />
        </div>

        {error && (
          <Alert variant="danger" className="mb-4 rounded">
            {error}
          </Alert>
        )}

        {loading ? (
          <div className="text-center">
            <Spinner animation="border" variant="primary" role="status" />
            <p className="mt-2 text-primary">Loading data...</p>
          </div>
        ) : (
          <div className="table-responsive shadow-sm rounded">
            <Table bordered hover className="align-middle text-center bg-white">
              <thead className="table-primary">
                <tr>
                  <th>Employee</th>
                  <th>Department</th>
                  <th>Salary</th>
                  <th>Work Hours</th>
                  <th>Month</th>
                  <th>Month Days</th>
                  <th>Attendance</th>
                  <th>Absence</th>
                  <th>Bonus</th>
                  <th>Deduction</th>
                  <th>Net Salary</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                {filteredEmployees.map(emp => (
                  <tr key={emp.id}>
                    <td className="d-flex align-items-center gap-2">
                      <img
                        src={getImageUrl(emp)}
                        alt={emp.full_name}
                        className="rounded-circle border border-primary shadow"
                        width="40"
                        height="40"
                      />
                      <span>{emp.full_name || "Unknown"}</span>
                    </td>
                    <td>{emp.dep_name ?? "-"}</td>
                    <td><Badge bg="primary">{emp.salary ?? "-"} EGP</Badge></td>
                    <td>{emp.working_hours_per_day ?? "-"}</td>
                    <td>{emp.payroll?.month ?? "-"}</td>
                    <td>{emp.payroll?.month_days ?? "-"}</td>
                    <td>{emp.payroll?.attended_days ?? "-"}</td>
                    <td>{emp.payroll?.absent_days ?? "-"}</td>
                    <td>{emp.payroll?.total_bonus_amount ?? "-"}</td>
                    <td>{emp.payroll?.total_deduction_amount ?? "-"}</td>
                    <td><Badge bg="success">{emp.payroll?.net_salary ?? "-"} EGP</Badge></td>
                    <td>
                      <Button size="sm" variant="primary" onClick={() => handleShow(emp)}>
                        View
                      </Button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </Table>
          </div>
        )}

        <Modal show={showModal} onHide={handleClose} centered className="fade-in">
          <Modal.Header closeButton>
            <Modal.Title className="fs-3 fw-bold">Employee Profile</Modal.Title>
          </Modal.Header>
          <Modal.Body>
            {selectedEmployee ? (
              <div className="container">
                <div className="text-center mb-4">
                  <img
                    src={getImageUrl(selectedEmployee)}
                    alt={selectedEmployee.full_name}
                    className="rounded-circle shadow border border-4 border-primary"
                    width="100"
                    height="100"
                  />
                  <h3 className="mt-2 fw-bold text-primary fs-3">{selectedEmployee.full_name || "Unknown"}</h3>
                  <Badge bg="primary" className="fs-6">{selectedEmployee.dep_name ?? "No Department"}</Badge>
                </div>

                <h5 className="fw-bold text-primary mb-3">Basic Information</h5>
                <div className="row g-3 mb-4">
                  <div className="col-md-4 slide-in">
                    <Card className="glass-card p-3 text-center">
                      <h6 className="mb-0 fs-4 fw-bold">Salary</h6>
                      <p className="text-success mb-0 fs-3">{selectedEmployee.salary ?? "N/A"} EGP</p>
                    </Card>
                  </div>
                  <div className="col-md-4 slide-in">
                    <Card className="glass-card p-3 text-center">
                      <h6 className="mb-0 fs-4 fw-bold">Work Hours</h6>
                      <p className="text-info mb-0 fs-3">{selectedEmployee.working_hours_per_day ?? "N/A"} hours/day</p>
                    </Card>
                  </div>
                  <div className="col-md-4 slide-in">
                    <Card className="glass-card p-3 text-center">
                      <h6 className="mb-0 fs-4 fw-bold">Payroll Month</h6>
                      <p className="text-primary mb-0 fs-3">{selectedEmployee.payroll?.month ?? "N/A"}</p>
                    </Card>
                  </div>
                  <div className="col-md-4 slide-in">
                    <Card className="glass-card p-3 text-center">
                      <h6 className="mb-0 fs-4 fw-bold">Check-In Time</h6>
                      <p className="mb-0 fs-3">{formatTime(selectedEmployee.default_check_in_time)}</p>
                    </Card>
                  </div>
                  <div className="col-md-4 slide-in">
                    <Card className="glass-card p-3 text-center">
                      <h6 className="mb-0 fs-4 fw-bold">Check-Out Time</h6>
                      <p className="mb-0 fs-3">{formatTime(selectedEmployee.default_check_out_time)}</p>
                    </Card>
                  </div>
                  <div className="col-md-4 slide-in">
                    <Card className="glass-card p-3 text-center">
                      <h6 className="mb-0 fs-4 fw-bold">Gender</h6>
                      <p className="mb-0 fs-3">{selectedEmployee.gender ?? "Not Specified"}</p>
                    </Card>
                  </div>
                  <div className="col-md-4 slide-in">
                    <Card className="glass-card p-3 text-center">
                      <h6 className="mb-0 fs-4 fw-bold">Nationality</h6>
                      <p className="mb-0 fs-3">{selectedEmployee.nationality ?? "Not Specified"}</p>
                    </Card>
                  </div>
                </div>

                <h5 className="fw-bold text-primary mb-3">General Settings</h5>
                <div className="row g-3 mb-4">
                  <div className="col-md-3 slide-in">
                    <Card className="glass-card p-3 text-center">
                      <h6 className="mb-0 fs-4 fw-bold">Deduction Type</h6>
                      <p className="mb-0 fs-3">{selectedEmployee.general_settings?.deduction_type ?? "Not Specified"}</p>
                    </Card>
                  </div>
                  <div className="col-md-3 slide-in">
                    <Card className="glass-card p-3 text-center">
                      <h6 className="mb-0 fs-4 fw-bold">Weekend Days</h6>
                      <p className="mb-0 fs-3">{formatWeekendDays(selectedEmployee.general_settings?.weekend_days)}</p>
                    </Card>
                  </div>
                  <div className="col-md-3 slide-in">
                    <Card className="glass-card p-3 text-center">
                      <h6 className="mb-0 fs-4 fw-bold">Deduction Value</h6>
                      <p className="mb-0 fs-3">{selectedEmployee.general_settings?.deduction_value ?? "0"} EGP</p>
                    </Card>
                  </div>
                  <div className="col-md-3 slide-in">
                    <Card className="glass-card p-3 text-center">
                      <h6 className="mb-0 fs-4 fw-bold">Overtime Type</h6>
                      <p className="mb-0 fs-3">{selectedEmployee.general_settings?.overtime_type ?? "Not Specified"}</p>
                    </Card>
                  </div>
                  <div className="col-md-3 slide-in">
                    <Card className="glass-card p-3 text-center">
                      <h6 className="mb-0 fs-4 fw-bold">Overtime Value</h6>
                      <p className="mb-0 fs-3">{selectedEmployee.general_settings?.overtime_value ?? "0"} EGP</p>
                    </Card>
                  </div>
                </div>

                <h5 className="fw-bold text-primary mb-3">Payroll Details</h5>
                <div className="row g-3 mb-4">
                  <div className="col-md-3 slide-in">
                    <Card className="glass-card p-3 text-center">
                      <h6 className="mb-0 fs-4 fw-bold">Month Days</h6>
                      <p className="mb-0 fs-3">{selectedEmployee.payroll?.month_days ?? "N/A"}</p>
                    </Card>
                  </div>
                  <div className="col-md-3 slide-in">
                    <Card className="glass-card p-3 text-center">
                      <h6 className="mb-0 fs-4 fw-bold">Attendance</h6>
                      <p className="mb-0 fs-3">{selectedEmployee.payroll?.attended_days ?? "N/A"}</p>
                    </Card>
                  </div>
                  <div className="col-md-3 slide-in">
                    <Card className="glass-card p-3 text-center">
                      <h6 className="mb-0 fs-4 fw-bold">Absence</h6>
                      <p className="mb-0 fs-3">{selectedEmployee.payroll?.absent_days ?? "N/A"}</p>
                    </Card>
                  </div>
                  <div className="col-md-3 slide-in">
                    <Card className="glass-card p-3 text-center">
                      <h6 className="mb-0 fs-4 fw-bold">Late Deduction</h6>
                      <p className="mb-0 fs-3">{selectedEmployee.payroll?.late_deduction_amount ?? "N/A"} EGP</p>
                    </Card>
                  </div>
                  <div className="col-md-6 slide-in">
                    <Card className="glass-card p-3 text-center">
                      <h6 className="mb-0 fs-4 fw-bold">Bonus</h6>
                      <p className="text-success mb-0 fs-3">{selectedEmployee.payroll?.total_bonus_amount ?? "N/A"} EGP</p>
                    </Card>
                  </div>
                  <div className="col-md-6 slide-in">
                    <Card className="glass-card p-3 text-center">
                      <h6 className="mb-0 fs-4 fw-bold">Total Deduction</h6>
                      <p className="text-danger mb-0 fs-3">{selectedEmployee.payroll?.total_deduction_amount ?? "N/A"} EGP</p>
                    </Card>
                  </div>
                  <div className="col-12 slide-in">
                    <Card className="glass-card p-3 text-center bg-primary text-white">
                      <h6 className="mb-0 fs-4 fw-bold">Net Salary</h6>
                      <h4 className="fw-bold mb-0 fs-2">{selectedEmployee.payroll?.net_salary ?? "N/A"} EGP</h4>
                    </Card>
                  </div>
                </div>
              </div>
            ) : (
              <p>Loading employee data...</p>
            )}
          </Modal.Body>
          <Modal.Footer>
            <Button variant="custom" className="footer-btn" onClick={handleClose}>
              Close
            </Button>
          </Modal.Footer>
        </Modal>
      </main>
    </div>
  );
}