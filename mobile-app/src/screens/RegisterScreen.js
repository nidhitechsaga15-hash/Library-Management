import React, { useState } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  Alert,
  ActivityIndicator,
  ScrollView,
  Platform,
} from 'react-native';
import DateTimePicker from '@react-native-community/datetimepicker';
import SearchableDropdown from '../components/SearchableDropdown';
import { useAuth } from '../context/AuthContext';
import { useNavigation } from '@react-navigation/native';
import { testConnection } from '../services/apiService';

export default function RegisterScreen() {
  const [formData, setFormData] = useState({
    name: '',
    father_name: '',
    mother_name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: '',
    student_id: '',
    phone: '',
    address: '',
    date_of_birth: '',
    course: '',
    branch: '',
    semester: '',
    year: '',
    batch: '',
  });
  const [loading, setLoading] = useState(false);
  const [showDatePicker, setShowDatePicker] = useState(false);
  const [selectedDate, setSelectedDate] = useState(new Date());
  const { register } = useAuth();
  const navigation = useNavigation();

  const courses = ['BCA', 'B.Tech', 'B.Sc', 'M.A', 'M.Sc', 'MBA', 'BBA', 'Other'];
  const branches = ['CSE', 'ECE', 'ME', 'CE', 'EE', 'IT', 'Other'];
  const semesters = ['1st Sem', '2nd Sem', '3rd Sem', '4th Sem', '5th Sem', '6th Sem', '7th Sem', '8th Sem'];
  const years = ['1st Year', '2nd Year', '3rd Year', '4th Year'];

  const handleRoleChange = (role) => {
    setFormData({ ...formData, role });
  };


  const handleRegister = async () => {
    // Validation
    if (!formData.name || !formData.email || !formData.password || !formData.role) {
      Alert.alert('Error', 'Please fill all required fields');
      return;
    }

    if (formData.password !== formData.password_confirmation) {
      Alert.alert('Error', 'Passwords do not match');
      return;
    }

    if (!formData.father_name || !formData.mother_name || !formData.address || !formData.date_of_birth) {
      Alert.alert('Error', 'Please fill all required fields (Father name, Mother name, Address, Date of Birth)');
      return;
    }

    if (formData.role === 'student') {
      if (!formData.course || !formData.branch || !formData.semester || !formData.year) {
        Alert.alert('Error', 'Please fill all student fields');
        return;
      }
    }

    // Generate simple token for mobile (captcha verification can be done on backend)
    const mobileCaptchaToken = `mobile_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;

    setLoading(true);
    console.log('Submitting registration form...', formData.email);
    
    try {
      const result = await register({ ...formData, captcha_token: mobileCaptchaToken });
      console.log('Registration result:', result);
      
      if (result.success) {
        Alert.alert('Success', 'Registration successful!');
        // Navigation will be handled by AuthContext
      } else {
        Alert.alert('Registration Failed', result.error || 'Registration failed');
      }
    } catch (error) {
      console.error('Registration error details:', error);
      let errorMsg = error.message || 'Registration failed. Please try again.';
      
      if (errorMsg.includes('timeout') || errorMsg.includes('ECONNABORTED')) {
        errorMsg = 'Request timeout. Please check:\n1. Internet connection\n2. Server is running (192.168.0.152:8000)\n3. Both devices on same network';
      } else if (errorMsg.includes('ECONNREFUSED') || errorMsg.includes('Network Error') || errorMsg.includes('Failed to fetch')) {
        errorMsg = 'Cannot connect to server.\n\nPlease check:\n1. Run: ./start-server-proper.sh\n2. IP address: 192.168.0.152:8000\n3. Same WiFi network\n4. Firewall allows port 8000';
      }
      
      Alert.alert('Registration Error', errorMsg);
    } finally {
      setLoading(false);
    }
  };

  return (
    <ScrollView style={styles.container} contentContainerStyle={styles.content}>
      <View style={styles.header}>
        <Text style={styles.logo}>📚</Text>
        <Text style={styles.title}>Create Account</Text>
        <Text style={styles.subtitle}>Sign up to get started</Text>
      </View>

      <View style={styles.form}>
        {/* Role Selection */}
        <Text style={styles.label}>Select Role *</Text>
        <View style={styles.roleContainer}>
          <TouchableOpacity
            style={[styles.roleButton, formData.role === 'admin' && styles.roleButtonActive]}
            onPress={() => handleRoleChange('admin')}
          >
            <Text style={[styles.roleText, formData.role === 'admin' && styles.roleTextActive]}>
              Admin
            </Text>
          </TouchableOpacity>
          <TouchableOpacity
            style={[styles.roleButton, formData.role === 'staff' && styles.roleButtonActive]}
            onPress={() => handleRoleChange('staff')}
          >
            <Text style={[styles.roleText, formData.role === 'staff' && styles.roleTextActive]}>
              Staff
            </Text>
          </TouchableOpacity>
          <TouchableOpacity
            style={[styles.roleButton, formData.role === 'student' && styles.roleButtonActive]}
            onPress={() => handleRoleChange('student')}
          >
            <Text style={[styles.roleText, formData.role === 'student' && styles.roleTextActive]}>
              Student
            </Text>
          </TouchableOpacity>
        </View>

        {/* Name */}
        <Text style={styles.label}>Full Name *</Text>
        <TextInput
          style={styles.input}
          placeholder="Enter your full name"
          value={formData.name}
          onChangeText={(text) => setFormData({ ...formData, name: text })}
        />

        {/* Father Name */}
        <Text style={styles.label}>Father's Name *</Text>
        <TextInput
          style={styles.input}
          placeholder="Enter father's name"
          value={formData.father_name}
          onChangeText={(text) => setFormData({ ...formData, father_name: text })}
        />

        {/* Mother Name */}
        <Text style={styles.label}>Mother's Name *</Text>
        <TextInput
          style={styles.input}
          placeholder="Enter mother's name"
          value={formData.mother_name}
          onChangeText={(text) => setFormData({ ...formData, mother_name: text })}
        />

        {/* Date of Birth */}
        <Text style={styles.label}>Date of Birth *</Text>
        <TouchableOpacity
          style={styles.input}
          onPress={() => setShowDatePicker(true)}
        >
          <Text style={formData.date_of_birth ? styles.dateText : styles.placeholderText}>
            {formData.date_of_birth || 'Select Date of Birth'}
          </Text>
        </TouchableOpacity>
        {showDatePicker && (
          <DateTimePicker
            value={selectedDate}
            mode="date"
            display={Platform.OS === 'ios' ? 'spinner' : 'default'}
            onChange={(event, date) => {
              setShowDatePicker(Platform.OS === 'ios');
              if (date) {
                setSelectedDate(date);
                const formattedDate = date.toISOString().split('T')[0];
                setFormData({ ...formData, date_of_birth: formattedDate });
              }
            }}
            maximumDate={new Date()}
          />
        )}

        {/* Address */}
        <Text style={styles.label}>Address *</Text>
        <TextInput
          style={[styles.input, styles.textArea]}
          placeholder="Enter your address"
          value={formData.address}
          onChangeText={(text) => setFormData({ ...formData, address: text })}
          multiline
          numberOfLines={3}
        />

        {/* Email */}
        <Text style={styles.label}>Email *</Text>
        <TextInput
          style={styles.input}
          placeholder="Enter your email"
          value={formData.email}
          onChangeText={(text) => setFormData({ ...formData, email: text })}
          keyboardType="email-address"
          autoCapitalize="none"
        />

        {/* Password */}
        <Text style={styles.label}>Password *</Text>
        <TextInput
          style={styles.input}
          placeholder="Enter password"
          value={formData.password}
          onChangeText={(text) => setFormData({ ...formData, password: text })}
          secureTextEntry
        />

        {/* Confirm Password */}
        <Text style={styles.label}>Confirm Password *</Text>
        <TextInput
          style={styles.input}
          placeholder="Confirm password"
          value={formData.password_confirmation}
          onChangeText={(text) => setFormData({ ...formData, password_confirmation: text })}
          secureTextEntry
        />

        {/* Student ID (if student) */}
        {formData.role === 'student' && (
          <>
            <Text style={styles.label}>Student ID</Text>
            <TextInput
              style={styles.input}
              placeholder="Enter student ID"
              value={formData.student_id}
              onChangeText={(text) => setFormData({ ...formData, student_id: text })}
            />

            <Text style={styles.label}>Course *</Text>
            <SearchableDropdown
              options={courses}
              selectedValue={formData.course}
              onSelect={(value) => setFormData({ ...formData, course: value })}
              placeholder="Select Course"
              style={styles.dropdown}
            />

            <Text style={styles.label}>Branch *</Text>
            <SearchableDropdown
              options={branches}
              selectedValue={formData.branch}
              onSelect={(value) => setFormData({ ...formData, branch: value })}
              placeholder="Select Branch"
              style={styles.dropdown}
            />

            <Text style={styles.label}>Semester *</Text>
            <SearchableDropdown
              options={semesters}
              selectedValue={formData.semester}
              onSelect={(value) => setFormData({ ...formData, semester: value })}
              placeholder="Select Semester"
              style={styles.dropdown}
            />

            <Text style={styles.label}>Year *</Text>
            <SearchableDropdown
              options={years}
              selectedValue={formData.year}
              onSelect={(value) => setFormData({ ...formData, year: value })}
              placeholder="Select Year"
              style={styles.dropdown}
            />

            <Text style={styles.label}>Batch</Text>
            <TextInput
              style={styles.input}
              placeholder="e.g., 2023-2026"
              value={formData.batch}
              onChangeText={(text) => setFormData({ ...formData, batch: text })}
            />
          </>
        )}

        {/* Phone */}
        <Text style={styles.label}>Phone</Text>
        <TextInput
          style={styles.input}
          placeholder="Enter phone number"
          value={formData.phone}
          onChangeText={(text) => setFormData({ ...formData, phone: text })}
          keyboardType="phone-pad"
        />

        {/* Captcha - Mobile apps can skip or use backend verification */}

        {/* Register Button */}
        <TouchableOpacity
          style={[styles.button, loading && styles.buttonDisabled]}
          onPress={handleRegister}
          disabled={loading}
        >
          {loading ? (
            <ActivityIndicator color="#fff" />
          ) : (
            <Text style={styles.buttonText}>Register</Text>
          )}
        </TouchableOpacity>

        {/* Login Link */}
        <TouchableOpacity
          style={styles.linkContainer}
          onPress={() => navigation.navigate('Login')}
        >
          <Text style={styles.linkText}>
            Already have an account? <Text style={styles.linkBold}>Login</Text>
          </Text>
        </TouchableOpacity>
      </View>

    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  content: {
    flexGrow: 1,
    padding: 20,
    paddingTop: 40,
  },
  header: {
    alignItems: 'center',
    marginBottom: 30,
  },
  logo: {
    fontSize: 60,
    marginBottom: 10,
  },
  title: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 5,
  },
  subtitle: {
    fontSize: 16,
    color: '#666',
  },
  form: {
    width: '100%',
  },
  label: {
    fontSize: 14,
    fontWeight: '600',
    color: '#333',
    marginBottom: 8,
    marginTop: 15,
  },
  roleContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 10,
  },
  roleButton: {
    flex: 1,
    padding: 12,
    marginHorizontal: 5,
    borderRadius: 8,
    backgroundColor: '#fff',
    borderWidth: 2,
    borderColor: '#e0e0e0',
    alignItems: 'center',
  },
  roleButtonActive: {
    backgroundColor: '#667eea',
    borderColor: '#667eea',
  },
  roleText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#666',
  },
  roleTextActive: {
    color: '#fff',
  },
  input: {
    backgroundColor: '#fff',
    borderRadius: 8,
    padding: 15,
    fontSize: 16,
    borderWidth: 1,
    borderColor: '#e0e0e0',
    marginBottom: 10,
  },
  textArea: {
    minHeight: 80,
    textAlignVertical: 'top',
  },
  captchaButton: {
    backgroundColor: '#fff',
    borderRadius: 8,
    padding: 15,
    borderWidth: 1,
    borderColor: '#667eea',
    alignItems: 'center',
    marginTop: 10,
  },
  captchaButtonText: {
    color: '#667eea',
    fontSize: 16,
    fontWeight: '600',
  },
  captchaVerified: {
    backgroundColor: '#d4edda',
    borderRadius: 8,
    padding: 15,
    marginTop: 10,
    alignItems: 'center',
  },
  captchaVerifiedText: {
    color: '#155724',
    fontSize: 14,
    fontWeight: '600',
  },
  button: {
    backgroundColor: '#667eea',
    borderRadius: 8,
    padding: 15,
    alignItems: 'center',
    marginTop: 20,
  },
  buttonDisabled: {
    opacity: 0.6,
  },
  buttonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '600',
  },
  linkContainer: {
    marginTop: 20,
    alignItems: 'center',
  },
  linkText: {
    color: '#666',
    fontSize: 14,
  },
  linkBold: {
    color: '#667eea',
    fontWeight: '600',
  },
  dateText: {
    fontSize: 16,
    color: '#333',
  },
  placeholderText: {
    fontSize: 16,
    color: '#999',
  },
  dropdown: {
    marginBottom: 10,
  },
});

