import React, { useState, useEffect, useRef } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  ActivityIndicator,
  RefreshControl,
  FlatList,
  Alert,
  Animated,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import { useAuth } from '../../context/AuthContext';
import { apiService } from '../../services/apiService';

export default function AdminDashboard() {
  const { user, logout } = useAuth();
  const navigation = useNavigation();
  const [stats, setStats] = useState(null);
  const [recentBorrows, setRecentBorrows] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const shimmerAnim = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    loadDashboard();
  }, []);

  useEffect(() => {
    if (loading) {
      Animated.loop(
        Animated.sequence([
          Animated.timing(shimmerAnim, {
            toValue: 1,
            duration: 1000,
            useNativeDriver: true,
          }),
          Animated.timing(shimmerAnim, {
            toValue: 0,
            duration: 1000,
            useNativeDriver: true,
          }),
        ])
      ).start();
    }
  }, [loading]);

  const loadDashboard = async () => {
    try {
      const data = await apiService.admin.getDashboard();
      setStats(data.stats || data);
      setRecentBorrows(data.recent_borrows || []);
    } catch (error) {
      console.error('Error loading dashboard:', error);
      // Only show alert if not refreshing (to avoid multiple alerts)
      if (!refreshing) {
        Alert.alert(
          'Connection Error',
          error.message || 'Failed to load dashboard. Please check your connection and try again.',
          [{ text: 'OK' }]
        );
      }
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const onRefresh = () => {
    setRefreshing(true);
    loadDashboard();
  };

  const handleActionPress = (action) => {
    switch (action) {
      case 'books':
        navigation.navigate('Books');
        break;
      case 'users':
        navigation.navigate('More', { screen: 'UserTypeSelection' });
        break;
      case 'borrows':
        navigation.navigate('Issue/Return');
        break;
      case 'fines':
        navigation.navigate('More', { screen: 'Fines' });
        break;
      case 'reports':
        navigation.navigate('More', { screen: 'Reports' });
        break;
      case 'chat':
        navigation.navigate('Chat');
        break;
      default:
        break;
    }
  };


  const renderBorrow = ({ item }) => {
    const statusColor = item.status === 'borrowed' ? '#ffc107' : '#28a745';
    const statusBgColor = item.status === 'borrowed' ? '#fff3cd' : '#d4edda';
    
    return (
      <View style={styles.borrowItem}>
        <View style={[styles.borrowAccentBar, { backgroundColor: statusColor }]} />
        <View style={styles.borrowContent}>
          <View style={styles.borrowInfo}>
            <Text style={styles.borrowBook}>{item.book?.title}</Text>
            <Text style={styles.borrowUser}>{item.user?.name}</Text>
            <Text style={styles.borrowDate}>
              Due: {new Date(item.due_date).toLocaleDateString()}
            </Text>
          </View>
          <View style={[styles.borrowIconContainer, { backgroundColor: `${statusColor}20` }]}>
            <Ionicons 
              name={item.status === 'borrowed' ? 'time-outline' : 'checkmark-circle'} 
              size={24} 
              color={statusColor} 
            />
          </View>
        </View>
      </View>
    );
  };

  const renderSkeletonStatCard = () => (
    <View style={styles.statCard}>
      <View style={[styles.statIconContainer, styles.skeletonBox]} />
      <View style={[styles.skeletonBox, { width: 40, height: 28, marginBottom: 5 }]} />
      <View style={[styles.skeletonBox, { width: 80, height: 14, marginBottom: 3 }]} />
      <View style={[styles.skeletonBox, { width: 60, height: 11 }]} />
    </View>
  );

  const renderSkeletonBorrowItem = () => (
    <View style={styles.borrowItem}>
      <View style={[styles.borrowAccentBar, styles.skeletonBox]} />
      <View style={styles.borrowContent}>
        <View style={styles.borrowInfo}>
          <View style={[styles.skeletonBox, { width: '80%', height: 16, marginBottom: 6 }]} />
          <View style={[styles.skeletonBox, { width: '60%', height: 14, marginBottom: 4 }]} />
          <View style={[styles.skeletonBox, { width: '40%', height: 12 }]} />
        </View>
        <View style={[styles.borrowIconContainer, styles.skeletonBox]} />
      </View>
    </View>
  );

  const SkeletonScreen = () => {
    const opacity = shimmerAnim.interpolate({
      inputRange: [0, 1],
      outputRange: [0.3, 0.7],
    });

    return (
      <View style={styles.container}>
        <View style={styles.header}>
          <View>
            <Text style={styles.greeting}>Welcome back,</Text>
            <Text style={styles.name}>{user?.name || 'Admin'}</Text>
          </View>
          <TouchableOpacity
            onPress={() => navigation.navigate('More', { screen: 'Notifications' })}
            style={styles.notificationButton}
          >
            <Ionicons name="notifications-outline" size={24} color="#fff" />
          </TouchableOpacity>
        </View>

        <ScrollView style={styles.content}>
          {/* Stats Cards Skeleton */}
          <View style={styles.statsContainer}>
            {[1, 2, 3, 4, 5, 6].map((item) => (
              <Animated.View key={item} style={{ opacity }}>
                {renderSkeletonStatCard()}
              </Animated.View>
            ))}
          </View>

          {/* Recent Borrows Skeleton */}
          <View style={styles.section}>
            <View style={styles.sectionHeader}>
              <Ionicons name="time-outline" size={20} color="#667eea" />
              <Text style={styles.sectionTitle}>Recent Borrows</Text>
            </View>
            {[1, 2, 3, 4].map((item) => (
              <Animated.View key={item} style={{ opacity }}>
                {renderSkeletonBorrowItem()}
              </Animated.View>
            ))}
          </View>

          {/* Quick Actions Skeleton */}
          <View style={styles.section}>
            <View style={styles.sectionHeader}>
              <Ionicons name="flash" size={20} color="#667eea" />
              <Text style={styles.sectionTitle}>Quick Actions</Text>
            </View>
            <View style={styles.actionsGrid}>
              {[1, 2, 3, 4, 5, 6].map((item) => (
                <Animated.View key={item} style={[styles.actionCard, { opacity }]}>
                  <View style={[styles.skeletonBox, { width: 32, height: 32, borderRadius: 16, marginBottom: 8 }]} />
                  <View style={[styles.skeletonBox, { width: 50, height: 12 }]} />
                </Animated.View>
              ))}
            </View>
          </View>
        </ScrollView>
      </View>
    );
  };

  if (loading) {
    return <SkeletonScreen />;
  }

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <View>
          <Text style={styles.greeting}>Welcome back,</Text>
          <Text style={styles.name}>{user?.name || 'Admin'}</Text>
        </View>
        <TouchableOpacity
          onPress={() => navigation.navigate('More', { screen: 'Notifications' })}
          style={styles.notificationButton}
        >
          <Ionicons name="notifications-outline" size={24} color="#fff" />
        </TouchableOpacity>
      </View>

      <ScrollView
        style={styles.content}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor="#667eea" />}
      >
        {/* Stats Cards - 6 Cards matching web view */}
        <View style={styles.statsContainer}>
          <View style={styles.statCard}>
            <View style={styles.statIconContainer}>
              <Ionicons name="book" size={24} color="#667eea" />
            </View>
            <Text style={styles.statValue}>{stats?.total_books || 0}</Text>
            <Text style={styles.statLabel}>Total Books</Text>
            <Text style={styles.statDesc}>All books in library</Text>
          </View>

          <View style={styles.statCard}>
            <View style={[styles.statIconContainer, { backgroundColor: '#fff3cd20' }]}>
              <Ionicons name="time" size={24} color="#ffc107" />
            </View>
            <Text style={styles.statValue}>{stats?.issued_books || 0}</Text>
            <Text style={styles.statLabel}>Issued Books</Text>
            <Text style={styles.statDesc}>Currently borrowed</Text>
          </View>

          <View style={styles.statCard}>
            <View style={[styles.statIconContainer, { backgroundColor: '#d4edda20' }]}>
              <Ionicons name="checkmark-circle" size={24} color="#28a745" />
            </View>
            <Text style={styles.statValue}>{stats?.returned_books || 0}</Text>
            <Text style={styles.statLabel}>Returned Books</Text>
            <Text style={styles.statDesc}>Successfully returned</Text>
          </View>

          <View style={styles.statCard}>
            <View style={[styles.statIconContainer, { backgroundColor: '#f8d7da20' }]}>
              <Ionicons name="warning" size={24} color="#dc3545" />
            </View>
            <Text style={[styles.statValue, { color: '#dc3545' }]}>{stats?.overdue_books || 0}</Text>
            <Text style={styles.statLabel}>Overdue Books</Text>
            <Text style={styles.statDesc}>Past due date</Text>
          </View>

          <View style={styles.statCard}>
            <View style={[styles.statIconContainer, { backgroundColor: '#d1ecf120' }]}>
              <Ionicons name="people" size={24} color="#17a2b8" />
            </View>
            <Text style={styles.statValue}>{stats?.total_students || 0}</Text>
            <Text style={styles.statLabel}>Total Students</Text>
            <Text style={styles.statDesc}>Registered students</Text>
          </View>

          <View style={styles.statCard}>
            <View style={[styles.statIconContainer, { backgroundColor: '#e2e3e520' }]}>
              <Ionicons name="person" size={24} color="#6c757d" />
            </View>
            <Text style={styles.statValue}>{stats?.total_staff || 0}</Text>
            <Text style={styles.statLabel}>Total Staff</Text>
            <Text style={styles.statDesc}>Library staff members</Text>
          </View>
        </View>

        {/* Recent Borrows */}
        <View style={styles.section}>
          <View style={styles.sectionHeader}>
            <Ionicons name="time-outline" size={20} color="#667eea" />
            <Text style={styles.sectionTitle}>Recent Borrows</Text>
          </View>
          {recentBorrows.length > 0 ? (
            <FlatList
              data={recentBorrows.slice(0, 4)}
              renderItem={renderBorrow}
              keyExtractor={(item) => item.id.toString()}
              scrollEnabled={false}
              showsVerticalScrollIndicator={false}
              ListEmptyComponent={
                <View style={styles.emptyContainer}>
                  <Text style={styles.emptyText}>No recent borrows</Text>
                </View>
              }
            />
          ) : (
            <View style={styles.emptyContainer}>
              <Ionicons name="archive-outline" size={48} color="#ccc" />
              <Text style={styles.emptyText}>No recent borrows</Text>
            </View>
          )}
        </View>

        {/* Quick Actions */}
        <View style={styles.section}>
          <View style={styles.sectionHeader}>
            <Ionicons name="flash" size={20} color="#667eea" />
            <Text style={styles.sectionTitle}>Quick Actions</Text>
          </View>
          <View style={styles.actionsGrid}>
            <TouchableOpacity style={styles.actionCard} onPress={() => handleActionPress('books')}>
              <Ionicons name="book-outline" size={32} color="#667eea" />
              <Text style={styles.actionText}>Books</Text>
            </TouchableOpacity>
            <TouchableOpacity style={styles.actionCard} onPress={() => handleActionPress('users')}>
              <Ionicons name="people-outline" size={32} color="#667eea" />
              <Text style={styles.actionText}>Users</Text>
            </TouchableOpacity>
            <TouchableOpacity style={styles.actionCard} onPress={() => handleActionPress('borrows')}>
              <Ionicons name="swap-horizontal-outline" size={32} color="#667eea" />
              <Text style={styles.actionText}>Borrows</Text>
            </TouchableOpacity>
            <TouchableOpacity style={styles.actionCard} onPress={() => handleActionPress('fines')}>
              <Ionicons name="cash-outline" size={32} color="#667eea" />
              <Text style={styles.actionText}>Fines</Text>
            </TouchableOpacity>
            <TouchableOpacity style={styles.actionCard} onPress={() => handleActionPress('reports')}>
              <Ionicons name="stats-chart-outline" size={32} color="#667eea" />
              <Text style={styles.actionText}>Reports</Text>
            </TouchableOpacity>
            <TouchableOpacity style={styles.actionCard} onPress={() => handleActionPress('chat')}>
              <Ionicons name="chatbubbles-outline" size={32} color="#667eea" />
              <Text style={styles.actionText}>Chat</Text>
            </TouchableOpacity>
          </View>
        </View>
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
    backgroundColor: '#667eea',
    padding: 20,
    paddingTop: 50,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  greeting: {
    color: '#fff',
    fontSize: 14,
    opacity: 0.9,
  },
  name: {
    color: '#fff',
    fontSize: 24,
    fontWeight: 'bold',
  },
  notificationButton: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: 'rgba(255,255,255,0.2)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  content: {
    flex: 1,
  },
  statsContainer: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    padding: 15,
    justifyContent: 'space-between',
  },
  statCard: {
    width: '48%',
    backgroundColor: '#fff',
    borderRadius: 12,
    padding: 15,
    marginBottom: 15,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  statIconContainer: {
    width: 48,
    height: 48,
    borderRadius: 24,
    backgroundColor: '#667eea20',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 10,
  },
  statValue: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#667eea',
    marginBottom: 5,
  },
  statLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: '#333',
    marginBottom: 3,
  },
  statDesc: {
    fontSize: 11,
    color: '#999',
  },
  section: {
    padding: 15,
    paddingTop: 0,
  },
  sectionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 15,
    gap: 8,
  },
  sectionTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#333',
  },
  borrowItem: {
    backgroundColor: '#fff',
    borderRadius: 12,
    marginBottom: 12,
    flexDirection: 'row',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
    overflow: 'hidden',
  },
  borrowAccentBar: {
    width: 4,
    backgroundColor: '#667eea',
  },
  borrowContent: {
    flex: 1,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 20,
  },
  borrowInfo: {
    flex: 1,
    marginRight: 15,
  },
  borrowBook: {
    fontSize: 16,
    fontWeight: '700',
    color: '#333',
    marginBottom: 6,
  },
  borrowUser: {
    fontSize: 14,
    color: '#666',
    marginBottom: 4,
  },
  borrowDate: {
    fontSize: 12,
    color: '#999',
  },
  borrowIconContainer: {
    width: 48,
    height: 48,
    borderRadius: 24,
    backgroundColor: '#667eea20',
    justifyContent: 'center',
    alignItems: 'center',
  },
  emptyContainer: {
    padding: 30,
    alignItems: 'center',
  },
  emptyText: {
    fontSize: 14,
    color: '#999',
    marginTop: 10,
  },
  actionsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
  },
  actionCard: {
    width: '31%',
    backgroundColor: '#fff',
    borderRadius: 12,
    padding: 20,
    marginBottom: 15,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  actionText: {
    fontSize: 12,
    color: '#333',
    fontWeight: '600',
    textAlign: 'center',
    marginTop: 8,
  },
  skeletonBox: {
    backgroundColor: '#e0e0e0',
    borderRadius: 8,
  },
});
