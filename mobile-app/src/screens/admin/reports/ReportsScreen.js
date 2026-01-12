import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  ActivityIndicator,
  RefreshControl,
  TouchableOpacity,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { apiService } from '../../../services/apiService';

export default function ReportsScreen({ navigation }) {
  const [reports, setReports] = useState(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  useEffect(() => {
    loadReports();
  }, []);

  const loadReports = async () => {
    try {
      const data = await apiService.admin.getReports();
      setReports(data);
    } catch (error) {
      console.error('Error loading reports:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const onRefresh = () => {
    setRefreshing(true);
    loadReports();
  };

  if (loading) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#667eea" />
      </View>
    );
  }


  return (
    <ScrollView
      style={styles.container}
      refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
    >
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Reports</Text>
      </View>

      <View style={styles.content}>
        {/* Report Cards */}
        <TouchableOpacity
          style={[styles.reportCard, styles.reportCardPrimary]}
          onPress={() => navigation.navigate('TotalBooksReport')}
        >
          <View style={styles.reportCardContent}>
            <View style={styles.reportCardText}>
              <Text style={styles.reportCardTitle}>Total Books Report</Text>
              <Text style={styles.reportCardDescription}>View all books with details</Text>
            </View>
            <View style={[styles.reportCardIcon, styles.reportCardIconPrimary]}>
              <Ionicons name="book" size={32} color="#667eea" />
            </View>
          </View>
        </TouchableOpacity>

        <TouchableOpacity
          style={[styles.reportCard, styles.reportCardSuccess]}
          onPress={() => navigation.navigate('BookIssueReport')}
        >
          <View style={styles.reportCardContent}>
            <View style={styles.reportCardText}>
              <Text style={styles.reportCardTitle}>Book Issue Report</Text>
              <Text style={styles.reportCardDescription}>View all book issues and returns</Text>
            </View>
            <View style={[styles.reportCardIcon, styles.reportCardIconSuccess]}>
              <Ionicons name="swap-horizontal" size={32} color="#28a745" />
            </View>
          </View>
        </TouchableOpacity>

        <TouchableOpacity
          style={[styles.reportCard, styles.reportCardDanger]}
          onPress={() => navigation.navigate('OverdueReport')}
        >
          <View style={styles.reportCardContent}>
            <View style={styles.reportCardText}>
              <Text style={styles.reportCardTitle}>Overdue Report</Text>
              <Text style={styles.reportCardDescription}>View all overdue books</Text>
            </View>
            <View style={[styles.reportCardIcon, styles.reportCardIconDanger]}>
              <Ionicons name="warning" size={32} color="#dc3545" />
            </View>
          </View>
        </TouchableOpacity>

        <TouchableOpacity
          style={[styles.reportCard, styles.reportCardWarning]}
          onPress={() => navigation.navigate('FinesReport')}
        >
          <View style={styles.reportCardContent}>
            <View style={styles.reportCardText}>
              <Text style={styles.reportCardTitle}>Fine Report</Text>
              <Text style={styles.reportCardDescription}>View all fines and payments</Text>
            </View>
            <View style={[styles.reportCardIcon, styles.reportCardIconWarning]}>
              <Ionicons name="cash" size={32} color="#ffc107" />
            </View>
          </View>
        </TouchableOpacity>

        <TouchableOpacity
          style={[styles.reportCard, styles.reportCardInfo]}
          onPress={() => navigation.navigate('StudentWiseReport')}
        >
          <View style={styles.reportCardContent}>
            <View style={styles.reportCardText}>
              <Text style={styles.reportCardTitle}>Student-wise Report</Text>
              <Text style={styles.reportCardDescription}>View student borrowing history</Text>
            </View>
            <View style={[styles.reportCardIcon, styles.reportCardIconInfo]}>
              <Ionicons name="people" size={32} color="#17a2b8" />
            </View>
          </View>
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
  centerContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  header: {
    backgroundColor: '#667eea',
    padding: 20,
    paddingTop: 50,
  },
  headerTitle: {
    color: '#fff',
    fontSize: 24,
    fontWeight: 'bold',
  },
  content: {
    padding: 15,
  },
  reportCard: {
    backgroundColor: '#fff',
    borderRadius: 12,
    padding: 20,
    marginBottom: 15,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
    borderLeftWidth: 4,
  },
  reportCardPrimary: {
    borderLeftColor: '#667eea',
  },
  reportCardSuccess: {
    borderLeftColor: '#28a745',
  },
  reportCardDanger: {
    borderLeftColor: '#dc3545',
  },
  reportCardWarning: {
    borderLeftColor: '#ffc107',
  },
  reportCardInfo: {
    borderLeftColor: '#17a2b8',
  },
  reportCardContent: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  reportCardText: {
    flex: 1,
    marginRight: 15,
  },
  reportCardTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 5,
  },
  reportCardDescription: {
    fontSize: 14,
    color: '#666',
  },
  reportCardIcon: {
    width: 60,
    height: 60,
    borderRadius: 30,
    justifyContent: 'center',
    alignItems: 'center',
  },
  reportCardIconPrimary: {
    backgroundColor: '#667eea20',
  },
  reportCardIconSuccess: {
    backgroundColor: '#28a74520',
  },
  reportCardIconDanger: {
    backgroundColor: '#dc354520',
  },
  reportCardIconWarning: {
    backgroundColor: '#ffc10720',
  },
  reportCardIconInfo: {
    backgroundColor: '#17a2b820',
  },
});
