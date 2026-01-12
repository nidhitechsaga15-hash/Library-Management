import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  TouchableOpacity,
  ActivityIndicator,
  RefreshControl,
  TextInput,
  Modal,
  ScrollView,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { apiService } from '../../../services/apiService';

export default function StudentsListScreen({ navigation }) {
  const [students, setStudents] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [showDetailModal, setShowDetailModal] = useState(false);
  const [selectedStudent, setSelectedStudent] = useState(null);

  useEffect(() => {
    loadStudents();
  }, []);

  const loadStudents = async () => {
    try {
      const allUsers = await apiService.admin.getUsers();
      const studentsList = allUsers.filter(user => user.role === 'student');
      setStudents(studentsList);
    } catch (error) {
      console.error('Error loading students:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const onRefresh = () => {
    setRefreshing(true);
    loadStudents();
  };

  const handleStudentPress = (student) => {
    setSelectedStudent(student);
    setShowDetailModal(true);
  };

  const filteredStudents = students.filter(student =>
    student.name?.toLowerCase().includes(searchQuery.toLowerCase()) ||
    student.email?.toLowerCase().includes(searchQuery.toLowerCase()) ||
    student.student_id?.toLowerCase().includes(searchQuery.toLowerCase())
  );

  const renderStudent = ({ item }) => (
    <TouchableOpacity
      style={styles.card}
      onPress={() => handleStudentPress(item)}
    >
      <View style={styles.cardContent}>
        <View style={styles.avatarContainer}>
          <Ionicons name="school" size={32} color="#667eea" />
        </View>
        <View style={styles.studentInfo}>
          <Text style={styles.studentName}>{item.name}</Text>
          <Text style={styles.studentEmail}>{item.email}</Text>
          {item.student_id && (
            <Text style={styles.studentId}>ID: {item.student_id}</Text>
          )}
          {item.course && (
            <Text style={styles.studentCourse}>{item.course}</Text>
          )}
        </View>
      </View>
      <Ionicons name="chevron-forward" size={20} color="#999" />
    </TouchableOpacity>
  );

  if (loading) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#667eea" />
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()}>
          <Ionicons name="arrow-back" size={24} color="#fff" />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Students</Text>
        <View style={{ width: 24 }} />
      </View>

      <View style={styles.searchContainer}>
        <Ionicons name="search" size={20} color="#999" style={styles.searchIcon} />
        <TextInput
          style={styles.searchInput}
          placeholder="Search students..."
          value={searchQuery}
          onChangeText={setSearchQuery}
        />
      </View>

      <FlatList
        data={filteredStudents}
        renderItem={renderStudent}
        keyExtractor={(item) => item.id.toString()}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
        contentContainerStyle={styles.listContent}
        ListEmptyComponent={
          <View style={styles.emptyContainer}>
            <Ionicons name="school-outline" size={64} color="#ccc" />
            <Text style={styles.emptyText}>No students found</Text>
          </View>
        }
      />

      {/* Student Detail Modal */}
      {selectedStudent && (
        <Modal
          visible={showDetailModal}
          animationType="slide"
          transparent={true}
          onRequestClose={() => setShowDetailModal(false)}
        >
          <View style={styles.modalOverlay}>
            <View style={styles.modalContent}>
              <View style={styles.modalHeader}>
                <Text style={styles.modalTitle}>Student Details</Text>
                <TouchableOpacity onPress={() => setShowDetailModal(false)}>
                  <Ionicons name="close" size={24} color="#333" />
                </TouchableOpacity>
              </View>
              <ScrollView style={styles.modalBody}>
                <View style={styles.detailCard}>
                  <View style={styles.detailHeader}>
                    <View style={styles.detailIcon}>
                      <Ionicons name="school" size={40} color="#667eea" />
                    </View>
                    <View style={styles.detailInfo}>
                      <Text style={styles.detailName}>{selectedStudent.name}</Text>
                      <Text style={styles.detailEmail}>{selectedStudent.email}</Text>
                    </View>
                  </View>
                </View>

                <View style={styles.detailSection}>
                  <Text style={styles.detailSectionTitle}>Basic Information</Text>
                  {selectedStudent.phone && (
                    <View style={styles.detailRow}>
                      <Text style={styles.detailLabel}>Phone</Text>
                      <Text style={styles.detailValue}>{selectedStudent.phone}</Text>
                    </View>
                  )}
                  {selectedStudent.address && (
                    <View style={styles.detailRow}>
                      <Text style={styles.detailLabel}>Address</Text>
                      <Text style={styles.detailValue}>{selectedStudent.address}</Text>
                    </View>
                  )}
                  {selectedStudent.date_of_birth && (
                    <View style={styles.detailRow}>
                      <Text style={styles.detailLabel}>Date of Birth</Text>
                      <Text style={styles.detailValue}>
                        {new Date(selectedStudent.date_of_birth).toLocaleDateString()}
                      </Text>
                    </View>
                  )}
                </View>

                <View style={styles.detailSection}>
                  <Text style={styles.detailSectionTitle}>Student Information</Text>
                  {selectedStudent.student_id && (
                    <View style={styles.detailRow}>
                      <Text style={styles.detailLabel}>Student ID</Text>
                      <Text style={styles.detailValue}>{selectedStudent.student_id}</Text>
                    </View>
                  )}
                  {selectedStudent.course && (
                    <View style={styles.detailRow}>
                      <Text style={styles.detailLabel}>Course</Text>
                      <Text style={styles.detailValue}>{selectedStudent.course}</Text>
                    </View>
                  )}
                  {selectedStudent.branch && (
                    <View style={styles.detailRow}>
                      <Text style={styles.detailLabel}>Branch</Text>
                      <Text style={styles.detailValue}>{selectedStudent.branch}</Text>
                    </View>
                  )}
                  {selectedStudent.semester && (
                    <View style={styles.detailRow}>
                      <Text style={styles.detailLabel}>Semester</Text>
                      <Text style={styles.detailValue}>{selectedStudent.semester}</Text>
                    </View>
                  )}
                  {selectedStudent.year && (
                    <View style={styles.detailRow}>
                      <Text style={styles.detailLabel}>Year</Text>
                      <Text style={styles.detailValue}>{selectedStudent.year}</Text>
                    </View>
                  )}
                  {selectedStudent.batch && (
                    <View style={styles.detailRow}>
                      <Text style={styles.detailLabel}>Batch</Text>
                      <Text style={styles.detailValue}>{selectedStudent.batch}</Text>
                    </View>
                  )}
                  {selectedStudent.father_name && (
                    <View style={styles.detailRow}>
                      <Text style={styles.detailLabel}>Father's Name</Text>
                      <Text style={styles.detailValue}>{selectedStudent.father_name}</Text>
                    </View>
                  )}
                  {selectedStudent.mother_name && (
                    <View style={styles.detailRow}>
                      <Text style={styles.detailLabel}>Mother's Name</Text>
                      <Text style={styles.detailValue}>{selectedStudent.mother_name}</Text>
                    </View>
                  )}
                </View>

                <View style={styles.detailSection}>
                  <Text style={styles.detailSectionTitle}>Status</Text>
                  <View style={styles.detailRow}>
                    <Text style={styles.detailLabel}>Status</Text>
                    <Text
                      style={[
                        styles.detailValue,
                        selectedStudent.is_active
                          ? styles.statusActive
                          : styles.statusInactive,
                      ]}
                    >
                      {selectedStudent.is_active ? 'Active' : 'Inactive'}
                    </Text>
                  </View>
                </View>
              </ScrollView>
            </View>
          </View>
        </Modal>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  centerContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  header: {
    backgroundColor: '#667eea',
    padding: 20,
    paddingTop: 50,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  headerTitle: {
    color: '#fff',
    fontSize: 20,
    fontWeight: 'bold',
  },
  searchContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#fff',
    margin: 15,
    borderRadius: 10,
    paddingHorizontal: 15,
    borderWidth: 1,
    borderColor: '#e0e0e0',
  },
  searchIcon: {
    marginRight: 10,
  },
  searchInput: {
    flex: 1,
    paddingVertical: 12,
    fontSize: 16,
  },
  listContent: {
    padding: 15,
  },
  card: {
    backgroundColor: '#fff',
    borderRadius: 12,
    padding: 15,
    marginBottom: 15,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  cardContent: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
  },
  avatarContainer: {
    width: 50,
    height: 50,
    borderRadius: 25,
    backgroundColor: '#f0f4ff',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 15,
  },
  studentInfo: {
    flex: 1,
  },
  studentName: {
    fontSize: 16,
    fontWeight: '600',
    color: '#333',
    marginBottom: 5,
  },
  studentEmail: {
    fontSize: 14,
    color: '#666',
    marginBottom: 3,
  },
  studentId: {
    fontSize: 12,
    color: '#999',
    marginBottom: 3,
  },
  studentCourse: {
    fontSize: 12,
    color: '#667eea',
    fontWeight: '500',
  },
  emptyContainer: {
    padding: 40,
    alignItems: 'center',
  },
  emptyText: {
    fontSize: 16,
    color: '#999',
    marginTop: 10,
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.5)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  modalContent: {
    backgroundColor: '#fff',
    borderRadius: 16,
    width: '95%',
    maxHeight: '90%',
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 20,
    borderBottomWidth: 1,
    borderBottomColor: '#e0e0e0',
  },
  modalTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#333',
  },
  modalBody: {
    padding: 20,
  },
  detailCard: {
    backgroundColor: '#f5f5f5',
    borderRadius: 12,
    padding: 20,
    marginBottom: 20,
  },
  detailHeader: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  detailIcon: {
    width: 60,
    height: 60,
    borderRadius: 30,
    backgroundColor: '#fff',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 15,
  },
  detailInfo: {
    flex: 1,
  },
  detailName: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 5,
  },
  detailEmail: {
    fontSize: 14,
    color: '#666',
  },
  detailSection: {
    marginBottom: 20,
  },
  detailSectionTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#333',
    marginBottom: 15,
  },
  detailRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#e0e0e0',
  },
  detailLabel: {
    fontSize: 14,
    color: '#999',
    flex: 1,
  },
  detailValue: {
    fontSize: 14,
    fontWeight: '600',
    color: '#333',
    flex: 1,
    textAlign: 'right',
  },
  statusActive: {
    color: '#28a745',
  },
  statusInactive: {
    color: '#dc3545',
  },
});

