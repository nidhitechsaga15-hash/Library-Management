import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  ActivityIndicator,
  FlatList,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';

export default function StudentDetailReportScreen({ navigation, route }) {
  const [student, setStudent] = useState(null);
  const [activeBorrows, setActiveBorrows] = useState([]);
  const [returnedBorrows, setReturnedBorrows] = useState([]);
  const [pendingFines, setPendingFines] = useState([]);
  const [paidFines, setPaidFines] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const loadData = () => {
      try {
        const params = route?.params || {};
        const studentData = params.studentData || {};
        
        console.log('StudentDetailReport - Received params:', params);
        console.log('StudentDetailReport - StudentData:', studentData);
        
        if (studentData && studentData.student) {
          setStudent(studentData.student);
          setActiveBorrows(studentData.active_borrows || []);
          setReturnedBorrows(studentData.returned_borrows || []);
          setPendingFines(studentData.pending_fines || []);
          setPaidFines(studentData.paid_fines || []);
        } else if (studentData && studentData.success && studentData.student) {
          // Handle case where response has success wrapper
          setStudent(studentData.student);
          setActiveBorrows(studentData.active_borrows || []);
          setReturnedBorrows(studentData.returned_borrows || []);
          setPendingFines(studentData.pending_fines || []);
          setPaidFines(studentData.paid_fines || []);
        } else {
          console.warn('StudentDetailReport - No student data found');
        }
      } catch (error) {
        console.error('StudentDetailReport - Error loading data:', error);
      } finally {
        setLoading(false);
      }
    };

    loadData();
  }, [route?.params]);

  const renderBorrow = ({ item }) => {
    if (!item) return null;
    
    // Handle case where book data might be in different structure
    const bookTitle = item.book?.title || item.book_id || 'N/A';
    const borrowDate = item.borrow_date ? new Date(item.borrow_date).toLocaleDateString() : 'N/A';
    const dueDate = item.due_date ? new Date(item.due_date).toLocaleDateString() : 'N/A';
    
    return (
      <View style={styles.borrowCard}>
        <Text style={styles.borrowTitle}>{bookTitle}</Text>
        <Text style={styles.borrowDetail}>
          Borrow Date: {borrowDate}
        </Text>
        <Text style={styles.borrowDetail}>
          Due Date: {dueDate}
        </Text>
        <Text style={[styles.status, item.status === 'borrowed' ? styles.statusBorrowed : styles.statusReturned]}>
          {item.status?.toUpperCase() || 'N/A'}
        </Text>
      </View>
    );
  };

  const renderFine = ({ item }) => {
    if (!item) return null;
    
    const fineAmount = item.amount || 0;
    const bookTitle = item.borrow?.book?.title || item.book_id || 'N/A';
    const fineDate = item.created_at ? new Date(item.created_at).toLocaleDateString() : 'N/A';
    
    return (
      <View style={styles.fineCard}>
        <Text style={styles.fineTitle}>₹{fineAmount}</Text>
        <Text style={styles.fineDetail}>
          Book: {bookTitle}
        </Text>
        <Text style={styles.fineDetail}>
          Date: {fineDate}
        </Text>
        <Text style={[styles.status, item.status === 'paid' ? styles.statusPaid : styles.statusPending]}>
          {item.status?.toUpperCase() || 'N/A'}
        </Text>
      </View>
    );
  };

  if (loading) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#667eea" />
      </View>
    );
  }

  if (!student) {
    return (
      <View style={styles.centerContainer}>
        <Text style={styles.errorText}>Student data not found</Text>
        <TouchableOpacity style={styles.backButton} onPress={() => navigation.goBack()}>
          <Text style={styles.backButtonText}>Go Back</Text>
        </TouchableOpacity>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()}>
          <Ionicons name="arrow-back" size={24} color="#fff" />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Student Details</Text>
        <View style={{ width: 24 }} />
      </View>

      <ScrollView style={styles.content}>
        {/* Student Info Card */}
        <View style={styles.infoCard}>
          <View style={styles.infoHeader}>
            <View style={styles.iconContainer}>
              <Ionicons name="person" size={40} color="#667eea" />
            </View>
            <View style={styles.infoText}>
              <Text style={styles.studentName}>{student.name}</Text>
              <Text style={styles.studentEmail}>{student.email}</Text>
              {student.student_id && (
                <Text style={styles.studentId}>ID: {student.student_id}</Text>
              )}
            </View>
          </View>

          <View style={styles.detailsGrid}>
            {student.course && (
              <View style={styles.detailItem}>
                <Text style={styles.detailLabel}>Course</Text>
                <Text style={styles.detailValue}>{student.course}</Text>
              </View>
            )}
            {student.branch && (
              <View style={styles.detailItem}>
                <Text style={styles.detailLabel}>Branch</Text>
                <Text style={styles.detailValue}>{student.branch}</Text>
              </View>
            )}
            {student.semester && (
              <View style={styles.detailItem}>
                <Text style={styles.detailLabel}>Semester</Text>
                <Text style={styles.detailValue}>{student.semester}</Text>
              </View>
            )}
            {student.year && (
              <View style={styles.detailItem}>
                <Text style={styles.detailLabel}>Year</Text>
                <Text style={styles.detailValue}>{student.year}</Text>
              </View>
            )}
          </View>
        </View>

        {/* Statistics */}
        <View style={styles.statsContainer}>
          <View style={styles.statCard}>
            <Text style={styles.statValue}>{activeBorrows.length}</Text>
            <Text style={styles.statLabel}>Active Borrows</Text>
          </View>
          <View style={styles.statCard}>
            <Text style={styles.statValue}>{returnedBorrows.length}</Text>
            <Text style={styles.statLabel}>Returned</Text>
          </View>
          <View style={styles.statCard}>
            <Text style={[styles.statValue, { color: '#dc3545' }]}>
              ₹{pendingFines.reduce((sum, fine) => sum + (fine.amount || 0), 0)}
            </Text>
            <Text style={styles.statLabel}>Pending Fines</Text>
          </View>
        </View>

        {/* Active Borrows */}
        {activeBorrows.length > 0 && (
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>Active Borrows ({activeBorrows.length})</Text>
            <FlatList
              data={activeBorrows}
              renderItem={renderBorrow}
              keyExtractor={(item, index) => item?.id?.toString() || index.toString()}
              scrollEnabled={false}
            />
          </View>
        )}

        {/* Returned Borrows */}
        {returnedBorrows.length > 0 && (
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>Returned Books ({returnedBorrows.length})</Text>
            <FlatList
              data={returnedBorrows}
              renderItem={renderBorrow}
              keyExtractor={(item, index) => item?.id?.toString() || index.toString()}
              scrollEnabled={false}
            />
          </View>
        )}

        {/* Pending Fines */}
        {pendingFines.length > 0 && (
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>Pending Fines ({pendingFines.length})</Text>
            <FlatList
              data={pendingFines}
              renderItem={renderFine}
              keyExtractor={(item, index) => item?.id?.toString() || index.toString()}
              scrollEnabled={false}
            />
          </View>
        )}

        {/* Paid Fines */}
        {paidFines.length > 0 && (
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>Paid Fines ({paidFines.length})</Text>
            <FlatList
              data={paidFines}
              renderItem={renderFine}
              keyExtractor={(item, index) => item?.id?.toString() || index.toString()}
              scrollEnabled={false}
            />
          </View>
        )}

        {student && activeBorrows.length === 0 && returnedBorrows.length === 0 && pendingFines.length === 0 && paidFines.length === 0 && (
          <View style={styles.emptyContainer}>
            <Text style={styles.emptyText}>No records found</Text>
          </View>
        )}
      </ScrollView>
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
    backgroundColor: '#17a2b8',
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
  content: {
    flex: 1,
    padding: 15,
  },
  infoCard: {
    backgroundColor: '#fff',
    borderRadius: 12,
    padding: 20,
    marginBottom: 15,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  infoHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 20,
  },
  iconContainer: {
    width: 60,
    height: 60,
    borderRadius: 30,
    backgroundColor: '#f0f4ff',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 15,
  },
  infoText: {
    flex: 1,
  },
  studentName: {
    fontSize: 22,
    fontWeight: 'bold',
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
  },
  detailsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 15,
  },
  detailItem: {
    width: '48%',
  },
  detailLabel: {
    fontSize: 12,
    color: '#999',
    marginBottom: 5,
  },
  detailValue: {
    fontSize: 14,
    fontWeight: '600',
    color: '#333',
  },
  statsContainer: {
    flexDirection: 'row',
    gap: 10,
    marginBottom: 15,
  },
  statCard: {
    flex: 1,
    backgroundColor: '#fff',
    borderRadius: 12,
    padding: 15,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  statValue: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#667eea',
    marginBottom: 5,
  },
  statLabel: {
    fontSize: 11,
    color: '#666',
  },
  section: {
    marginBottom: 20,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#333',
    marginBottom: 10,
  },
  borrowCard: {
    backgroundColor: '#fff',
    borderRadius: 12,
    padding: 15,
    marginBottom: 10,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  borrowTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 8,
  },
  borrowDetail: {
    fontSize: 14,
    color: '#666',
    marginBottom: 5,
  },
  fineCard: {
    backgroundColor: '#fff',
    borderRadius: 12,
    padding: 15,
    marginBottom: 10,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  fineTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#ffc107',
    marginBottom: 8,
  },
  fineDetail: {
    fontSize: 14,
    color: '#666',
    marginBottom: 5,
  },
  status: {
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 12,
    fontSize: 11,
    fontWeight: '600',
    alignSelf: 'flex-start',
    marginTop: 10,
  },
  statusBorrowed: {
    backgroundColor: '#d1ecf1',
    color: '#0c5460',
  },
  statusReturned: {
    backgroundColor: '#d4edda',
    color: '#155724',
  },
  statusPaid: {
    backgroundColor: '#d4edda',
    color: '#155724',
  },
  statusPending: {
    backgroundColor: '#fff3cd',
    color: '#856404',
  },
  emptyContainer: {
    padding: 40,
    alignItems: 'center',
  },
  emptyText: {
    fontSize: 16,
    color: '#999',
  },
  errorText: {
    fontSize: 16,
    color: '#dc3545',
    marginBottom: 20,
  },
  backButton: {
    backgroundColor: '#667eea',
    paddingHorizontal: 20,
    paddingVertical: 12,
    borderRadius: 8,
  },
  backButtonText: {
    color: '#fff',
    fontWeight: '600',
  },
});

