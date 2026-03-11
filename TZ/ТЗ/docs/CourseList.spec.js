import { mount } from '@vue/test-utils'
import CourseList from '@/components/CourseList.vue'

describe('CourseList', () => {
  test('renders empty state', () => {
    const wrapper = mount(CourseList, { props: { courses: [] } })
    expect(wrapper.text()).toContain('No courses yet')
  })
})