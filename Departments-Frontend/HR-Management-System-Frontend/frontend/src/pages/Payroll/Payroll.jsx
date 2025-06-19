
import React, { useEffect, useState, useCallback } from "react";
import { Table, Button, Modal, Form, Spinner, Badge, Card, Alert } from "react-bootstrap";
import { FaUserCircle, FaTimes } from "react-icons/fa";
import { FaUser } from "react-icons/fa";
import api from "../../api"; // Adjust path as needed
import "./Payroll.css";

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
    return time.split(':').slice(0, 2).join(':');
  };

  const formatWeekendDays = (days) => {
    return Array.isArray(days) && days.length > 0 ? days.join(", ") : "Not Specified";
  };

  return (
    <div className="d-flex" style={{ minHeight: "100vh" }}>
     

      <main className="flex-grow-1 p-4 bg-light">
        <div className="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
          <h2 className="fw-bold text-primary mb-3 mb-md-0">💼 Payroll Dashboard</h2>
          <Form.Control
            type="text"
            placeholder="🔎 Search by name..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="search-input"
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
                        className="employee-avatar"
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

        <Modal show={showModal} onHide={handleClose} centered size="lg" className="fade-in compact-modal">
          <Modal.Header className="bg-primary text-white d-flex justify-content-between align-items-center">
            <Modal.Title className="fs-4 fw-bold d-flex align-items-center gap-2">
              <FaUserCircle className="text-white" /> Employee Profile
            </Modal.Title>
            <Button variant="link" onClick={handleClose} className="text-white fs-4 p-0">
              <FaTimes />
            </Button>
          </Modal.Header>

          <Modal.Body>
            {selectedEmployee ? (
              <div className="container-fluid">
                <div className="text-center mb-3">
                  <img
                    src={getImageUrl(selectedEmployee)}
                    alt={selectedEmployee.full_name}
                    className="employee-profile-avatar"
                  />
                  <h3 className="mt-1 fw-bold text-primary fs-5">{selectedEmployee.full_name || "Unknown"}</h3>
                  <Badge bg="primary" className="fs-6">{selectedEmployee.dep_name ?? "No Department"}</Badge>
                </div>

                

                <div className="section-divider" />
                <h5 className="fw-bold text-primary mb-2 fs-6">Work Details</h5>
                <div className="row g-2 mb-3">
                  <div className ="col-12 col-md-4 slide-in">
                    <Card className="glass-card p-2">
                      <h6 className="mb-0 fs-6 fw-bold">Salary</h6>
                      <p className="text-success mb-0 fs-6">{selectedEmployee.salary ?? "N/A"} EGP</p>
                    </Card>
                  </div>
                  <div className="col-12 col-md-4 slide-in">
                    <Card className="glass-card p-2">
                      <h6 className="mb-0 fs-6 fw-bold">Work Hours</h6>
                      <p className="text-info mb-0 fs-6">{selectedEmployee.working_hours_per_day ?? "N/A"} hours/day</p>
                    </Card>
                  </div>
                  <div className="col-12 col-md-4 slide-in">
                    <Card className="glass-card p-2">
                      <h6 className="mb-0 fs-6 fw-bold">Payroll Month</h6>
                      <p className="text-primary mb-0 fs-6">{selectedEmployee.payroll?.month ?? "N/A"}</p>
                    </Card>
                  </div>
                  <div className="col-12 col-md-6 slide-in">
                    <Card className="glass-card p-2">
                      <h6 className="mb-0 fs-6 fw-bold">Check-In Time</h6>
                      <p className="mb-0 fs-6">{formatTime(selectedEmployee.default_check_in_time)}</p>
                    </Card>
                  </div>
                  <div className="col-12 col-md-6 slide-in">
                    <Card className="glass-card p-2">
                      <h6 className="mb-0 fs-6 fw-bold">Check-Out Time</h6>
                      <p className="mb-0 fs-6">{formatTime(selectedEmployee.default_check_out_time)}</p>
                    </Card>
                  </div>
                </div>

              <div className="section-divider" />
<h5 className="fw-bold text-primary mb-2 fs-6">Payroll Summary</h5>
<div className="row g-2 mb-3">

  <div className="col-12 col-md-4 slide-in">
    <Card className="glass-card p-2">
      <h6 className="mb-0 fs-6 fw-bold">Month</h6>
      <p className="mb-0 fs-6">{selectedEmployee.payroll?.month ?? "N/A"}</p>
    </Card>
  </div>

  <div className="col-12 col-md-4 slide-in">
    <Card className="glass-card p-2">
      <h6 className="mb-0 fs-6 fw-bold">Month Days</h6>
      <p className="mb-0 fs-6">{selectedEmployee.payroll?.month_days ?? "N/A"}</p>
    </Card>
  </div>

  <div className="col-12 col-md-4 slide-in">
    <Card className="glass-card p-2">
      <h6 className="mb-0 fs-6 fw-bold">Attendance</h6>
      <p className="mb-0 fs-6">{selectedEmployee.payroll?.attended_days ?? "N/A"}</p>
    </Card>
  </div>

  <div className="col-12 col-md-4 slide-in">
    <Card className="glass-card p-2">
      <h6 className="mb-0 fs-6 fw-bold">Absence</h6>
      <p className="mb-0 fs-6">{selectedEmployee.payroll?.absent_days ?? "N/A"}</p>
    </Card>
  </div>



  <div className="col-12 col-md-4 slide-in">
    <Card className="glass-card p-2">
      <h6 className="mb-0 fs-6 fw-bold">Bonus</h6>
      <p className="text-success mb-0 fs-6">{selectedEmployee.payroll?.total_bonus_amount ?? "N/A"} EGP</p>
    </Card>
  </div>

  <div className="col-12 col-md-4 slide-in">
    <Card className="glass-card p-2">
      <h6 className="mb-0 fs-6 fw-bold">Late Deduction</h6>
      <p className="mb-0 fs-6">{selectedEmployee.payroll?.late_deduction_amount ?? "N/A"} EGP</p>
    </Card>
  </div>

 

  <div className="col-12 col-md-4 slide-in">
    <Card className="glass-card p-2">
      <h6 className="mb-0 fs-6 fw-bold">Absence Deduction</h6>
      <p className="mb-0 fs-6">{selectedEmployee.payroll?.absence_deduction_amount ?? "N/A"} EGP</p>
    </Card>
  </div>

  <div className="col-12 col-md-4 slide-in">
    <Card className="glass-card p-2">
      <h6 className="mb-0 fs-6 fw-bold">Total Deduction</h6>
      <p className="text-danger mb-0 fs-6">{selectedEmployee.payroll?.total_deduction_amount ?? "N/A"} EGP</p>
    </Card>
  </div>

  <div className="col-12 slide-in">
    <Card className="glass-card p-2 bg-primary text-white">
      <h6 className="mb-0 fs-6 fw-bold">Net Salary</h6>
      <h4 className="fw-bold mb-0 fs-5">{selectedEmployee.payroll?.net_salary ?? "N/A"} EGP</h4>
    </Card>
  </div>

</div>

              </div>
            ) : (
              <p>Loading employee data...</p>
            )}
          </Modal.Body>
        </Modal>
      </main>
    </div>
  );
}


