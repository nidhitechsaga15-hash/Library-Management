import React from 'react';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { createStackNavigator } from '@react-navigation/stack';
import { Ionicons } from '@expo/vector-icons';
import { View, Text, TouchableOpacity, ScrollView, StyleSheet, Alert } from 'react-native';
import { useAuth } from '../context/AuthContext';

// Admin Screens
import AdminDashboard from '../screens/dashboards/AdminDashboard';
import BooksScreen from '../screens/admin/books/BooksScreen';
import CreateBookScreen from '../screens/admin/books/CreateBookScreen';
import EditBookScreen from '../screens/admin/books/EditBookScreen';
import BookDetailsScreen from '../screens/admin/books/BookDetailsScreen';
import AuthorsScreen from '../screens/admin/authors/AuthorsScreen';
import CreateAuthorScreen from '../screens/admin/authors/CreateAuthorScreen';
import EditAuthorScreen from '../screens/admin/authors/EditAuthorScreen';
import AuthorDetailsScreen from '../screens/admin/authors/AuthorDetailsScreen';
import CategoriesScreen from '../screens/admin/categories/CategoriesScreen';
import CreateCategoryScreen from '../screens/admin/categories/CreateCategoryScreen';
import EditCategoryScreen from '../screens/admin/categories/EditCategoryScreen';
import CategoryDetailsScreen from '../screens/admin/categories/CategoryDetailsScreen';
import UsersScreen from '../screens/admin/users/UsersScreen';
import UserTypeSelectionScreen from '../screens/admin/users/UserTypeSelectionScreen';
import StudentsListScreen from '../screens/admin/users/StudentsListScreen';
import StaffListScreen from '../screens/admin/users/StaffListScreen';
import BorrowsScreen from '../screens/admin/borrows/BorrowsScreen';
import FinesScreen from '../screens/admin/fines/FinesScreen';
import BookRequestsScreen from '../screens/admin/book-requests/BookRequestsScreen';
import ReportsScreen from '../screens/admin/reports/ReportsScreen';
import TotalBooksReportScreen from '../screens/admin/reports/TotalBooksReportScreen';
import BookIssueReportScreen from '../screens/admin/reports/BookIssueReportScreen';
import OverdueReportScreen from '../screens/admin/reports/OverdueReportScreen';
import FinesReportScreen from '../screens/admin/reports/FinesReportScreen';
import StudentWiseReportScreen from '../screens/admin/reports/StudentWiseReportScreen';
import StudentDetailReportScreen from '../screens/admin/reports/StudentDetailReportScreen';
import LibraryCardsScreen from '../screens/admin/library-cards/LibraryCardsScreen';
import QRScannerScreen from '../screens/admin/books/QRScannerScreen';
import ChatScreen from '../screens/shared/chat/ChatScreen';
import NotificationsScreen from '../screens/shared/notifications/NotificationsScreen';
import ProfileScreen from '../screens/shared/profile/ProfileScreen';

const Tab = createBottomTabNavigator();
const Stack = createStackNavigator();

function AdminTabs() {
  return (
    <Tab.Navigator
      screenOptions={{
        tabBarActiveTintColor: '#667eea',
        tabBarInactiveTintColor: '#666',
        headerShown: false,
      }}
    >
      <Tab.Screen
        name="Dashboard"
        component={AdminDashboard}
        options={{
          tabBarIcon: ({ color, size }) => (
            <Ionicons name="home" size={size} color={color} />
          ),
        }}
      />
      <Tab.Screen
        name="Books"
        component={BooksScreen}
        options={{
          tabBarIcon: ({ color, size }) => (
            <Ionicons name="book" size={size} color={color} />
          ),
        }}
      />
      <Tab.Screen
        name="Issue/Return"
        component={BorrowsScreen}
        options={{
          tabBarIcon: ({ color, size }) => (
            <Ionicons name="swap-horizontal" size={size} color={color} />
          ),
          title: 'Issue/Return',
        }}
      />
      <Tab.Screen
        name="Chat"
        component={ChatScreen}
        options={{
          tabBarIcon: ({ color, size }) => (
            <Ionicons name="chatbubbles" size={size} color={color} />
          ),
          tabBarBadge: null, // Will be updated with unread count
        }}
      />
      <Tab.Screen
        name="More"
        component={AdminMoreStack}
        options={{
          tabBarIcon: ({ color, size }) => (
            <Ionicons name="menu" size={size} color={color} />
          ),
        }}
      />
    </Tab.Navigator>
  );
}

function AdminMoreStack() {
  return (
    <Stack.Navigator
      screenOptions={{
        headerStyle: {
          backgroundColor: '#667eea',
        },
        headerTintColor: '#fff',
        headerTitleStyle: {
          fontWeight: 'bold',
        },
      }}
    >
      <Stack.Screen name="MoreMenu" component={AdminMoreMenu} options={{ title: 'More' }} />
      <Stack.Screen name="Authors" component={AuthorsScreen} />
      <Stack.Screen name="CreateAuthor" component={CreateAuthorScreen} options={{ title: 'Add Author' }} />
      <Stack.Screen name="EditAuthor" component={EditAuthorScreen} options={{ title: 'Edit Author' }} />
      <Stack.Screen name="AuthorDetails" component={AuthorDetailsScreen} options={{ title: 'Author Details' }} />
      <Stack.Screen name="Categories" component={CategoriesScreen} />
      <Stack.Screen name="CreateCategory" component={CreateCategoryScreen} options={{ title: 'Add Category' }} />
      <Stack.Screen name="EditCategory" component={EditCategoryScreen} options={{ title: 'Edit Category' }} />
      <Stack.Screen name="CategoryDetails" component={CategoryDetailsScreen} options={{ title: 'Category Details' }} />
      <Stack.Screen name="Users" component={UsersScreen} />
      <Stack.Screen name="UserTypeSelection" component={UserTypeSelectionScreen} options={{ title: 'Select User Type' }} />
      <Stack.Screen name="StudentsList" component={StudentsListScreen} options={{ title: 'Students' }} />
      <Stack.Screen name="StaffList" component={StaffListScreen} options={{ title: 'Staff' }} />
      <Stack.Screen name="Fines" component={FinesScreen} />
      <Stack.Screen name="BookRequests" component={BookRequestsScreen} />
      <Stack.Screen name="Reports" component={ReportsScreen} />
      <Stack.Screen name="TotalBooksReport" component={TotalBooksReportScreen} options={{ title: 'Total Books Report' }} />
      <Stack.Screen name="BookIssueReport" component={BookIssueReportScreen} options={{ title: 'Book Issue Report' }} />
      <Stack.Screen name="OverdueReport" component={OverdueReportScreen} options={{ title: 'Overdue Report' }} />
      <Stack.Screen name="FinesReport" component={FinesReportScreen} options={{ title: 'Fine Report' }} />
      <Stack.Screen name="StudentWiseReport" component={StudentWiseReportScreen} options={{ title: 'Student-wise Report' }} />
      <Stack.Screen name="StudentDetailReport" component={StudentDetailReportScreen} options={{ title: 'Student Details' }} />
      <Stack.Screen name="LibraryCards" component={LibraryCardsScreen} />
      <Stack.Screen name="CreateBook" component={CreateBookScreen} options={{ title: 'Add Book' }} />
      <Stack.Screen name="EditBook" component={EditBookScreen} options={{ title: 'Edit Book' }} />
      <Stack.Screen name="BookDetails" component={BookDetailsScreen} options={{ title: 'Book Details' }} />
      <Stack.Screen name="QRScanner" component={QRScannerScreen} options={{ title: 'Scan QR Code' }} />
      <Stack.Screen name="Notifications" component={NotificationsScreen} />
      <Stack.Screen name="Profile" component={ProfileScreen} />
    </Stack.Navigator>
  );
}

function AdminMoreMenu({ navigation }) {
  const { logout } = useAuth();

  const handleLogout = () => {
    Alert.alert(
      'Logout',
      'Are you sure you want to logout?',
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Logout',
          style: 'destructive',
          onPress: async () => {
            await logout();
          },
        },
      ]
    );
  };

  const handleNavigate = (screenName) => {
    try {
      navigation.navigate(screenName);
    } catch (error) {
      console.error('Navigation error:', error);
      Alert.alert('Error', `Failed to navigate to ${screenName}`);
    }
  };

  const handleNavigateToTab = (tabName) => {
    try {
      // Navigate to parent tab navigator
      navigation.getParent()?.navigate(tabName);
    } catch (error) {
      console.error('Navigation error:', error);
      // Fallback: try direct navigation
      try {
        navigation.navigate(tabName);
      } catch (e) {
        Alert.alert('Error', `Failed to navigate to ${tabName}`);
      }
    }
  };

  return (
    <View style={styles.container}>
      <ScrollView>
        <TouchableOpacity 
          style={styles.menuItem} 
          onPress={() => handleNavigate('QRScanner')}
          activeOpacity={0.7}
        >
          <Ionicons name="qr-code" size={24} color="#667eea" />
          <Text style={styles.menuText}>Scan QR Code</Text>
          <Ionicons name="chevron-forward" size={20} color="#ccc" style={styles.chevron} />
        </TouchableOpacity>
        <TouchableOpacity 
          style={styles.menuItem} 
          onPress={() => handleNavigate('CreateBook')}
          activeOpacity={0.7}
        >
          <Ionicons name="add-circle" size={24} color="#667eea" />
          <Text style={styles.menuText}>Add Book</Text>
          <Ionicons name="chevron-forward" size={20} color="#ccc" style={styles.chevron} />
        </TouchableOpacity>
        <TouchableOpacity 
          style={styles.menuItem} 
          onPress={() => handleNavigate('Authors')}
          activeOpacity={0.7}
        >
          <Ionicons name="person" size={24} color="#667eea" />
          <Text style={styles.menuText}>Authors</Text>
          <Ionicons name="chevron-forward" size={20} color="#ccc" style={styles.chevron} />
        </TouchableOpacity>
        <TouchableOpacity 
          style={styles.menuItem} 
          onPress={() => handleNavigate('Categories')}
          activeOpacity={0.7}
        >
          <Ionicons name="folder" size={24} color="#667eea" />
          <Text style={styles.menuText}>Categories</Text>
          <Ionicons name="chevron-forward" size={20} color="#ccc" style={styles.chevron} />
        </TouchableOpacity>
        <TouchableOpacity 
          style={styles.menuItem} 
          onPress={() => handleNavigate('Users')}
          activeOpacity={0.7}
        >
          <Ionicons name="people" size={24} color="#667eea" />
          <Text style={styles.menuText}>Users</Text>
          <Ionicons name="chevron-forward" size={20} color="#ccc" style={styles.chevron} />
        </TouchableOpacity>
        <TouchableOpacity 
          style={styles.menuItem} 
          onPress={() => handleNavigateToTab('Issue/Return')}
          activeOpacity={0.7}
        >
          <Ionicons name="swap-horizontal" size={24} color="#667eea" />
          <Text style={styles.menuText}>Issue/Return</Text>
          <Ionicons name="chevron-forward" size={20} color="#ccc" style={styles.chevron} />
        </TouchableOpacity>
        <TouchableOpacity 
          style={styles.menuItem} 
          onPress={() => handleNavigate('Fines')}
          activeOpacity={0.7}
        >
          <Ionicons name="cash" size={24} color="#667eea" />
          <Text style={styles.menuText}>Fines</Text>
          <Ionicons name="chevron-forward" size={20} color="#ccc" style={styles.chevron} />
        </TouchableOpacity>
        <TouchableOpacity 
          style={styles.menuItem} 
          onPress={() => handleNavigate('BookRequests')}
          activeOpacity={0.7}
        >
          <Ionicons name="hand-left" size={24} color="#667eea" />
          <Text style={styles.menuText}>Book Requests</Text>
          <Ionicons name="chevron-forward" size={20} color="#ccc" style={styles.chevron} />
        </TouchableOpacity>
        <TouchableOpacity 
          style={styles.menuItem} 
          onPress={() => handleNavigate('Reports')}
          activeOpacity={0.7}
        >
          <Ionicons name="stats-chart" size={24} color="#667eea" />
          <Text style={styles.menuText}>Reports</Text>
          <Ionicons name="chevron-forward" size={20} color="#ccc" style={styles.chevron} />
        </TouchableOpacity>
        <TouchableOpacity 
          style={styles.menuItem} 
          onPress={() => handleNavigate('LibraryCards')}
          activeOpacity={0.7}
        >
          <Ionicons name="card" size={24} color="#667eea" />
          <Text style={styles.menuText}>Library Cards</Text>
          <Ionicons name="chevron-forward" size={20} color="#ccc" style={styles.chevron} />
        </TouchableOpacity>
        <TouchableOpacity 
          style={styles.menuItem} 
          onPress={() => handleNavigate('Notifications')}
          activeOpacity={0.7}
        >
          <Ionicons name="notifications" size={24} color="#667eea" />
          <Text style={styles.menuText}>Notifications</Text>
          <Ionicons name="chevron-forward" size={20} color="#ccc" style={styles.chevron} />
        </TouchableOpacity>
        <TouchableOpacity 
          style={styles.menuItem} 
          onPress={() => handleNavigate('Profile')}
          activeOpacity={0.7}
        >
          <Ionicons name="person-circle" size={24} color="#667eea" />
          <Text style={styles.menuText}>Profile</Text>
          <Ionicons name="chevron-forward" size={20} color="#ccc" style={styles.chevron} />
        </TouchableOpacity>
        <TouchableOpacity
          style={[styles.menuItem, styles.logoutMenuItem]}
          onPress={handleLogout}
          activeOpacity={0.7}
        >
          <Ionicons name="log-out-outline" size={24} color="#dc3545" />
          <Text style={[styles.menuText, styles.logoutText]}>Logout</Text>
        </TouchableOpacity>
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  menuItem: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 15,
    backgroundColor: '#fff',
    borderBottomWidth: 1,
    borderBottomColor: '#e0e0e0',
    minHeight: 56,
  },
  menuText: {
    marginLeft: 15,
    fontSize: 16,
    color: '#333',
    flex: 1,
  },
  chevron: {
    marginLeft: 'auto',
  },
  logoutMenuItem: {
    borderTopWidth: 1,
    borderTopColor: '#e0e0e0',
    marginTop: 10,
    paddingTop: 15,
  },
  logoutText: {
    color: '#dc3545',
  },
});

export default AdminTabs;

