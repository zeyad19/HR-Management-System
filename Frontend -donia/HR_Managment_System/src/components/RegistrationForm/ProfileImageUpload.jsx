import React, { useState } from 'react';
import './ProfileImageUpload.css'; // Assuming you have some styles for the component

const ProfileImageUpload = ({ onChange }) => {
  const [preview, setPreview] = useState(null);

  const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
      // Trigger the parent onChange with a custom event object
      onChange({
        target: {
          name: 'profileImage',
          type: 'file',
          files: [file],
        },
      });

      // Update local preview
      const reader = new FileReader();
      reader.onloadend = () => {
        setPreview(reader.result);
      };
      reader.readAsDataURL(file);
    }
  };

  return (
    <div className="profile-image-upload">
      <input
        type="file"
        accept="image/*"
        name="profileImage"
        onChange={handleImageChange}
      />
      {preview && (
        <div className="image-preview">
          <img src={preview} alt="Profile Preview" />
        </div>
      )}
    </div>
  );
};

export default ProfileImageUpload;