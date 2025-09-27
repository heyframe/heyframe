/** View Adapter */
/** Initializer */
import initializers from '@/app/init';
import postInitializer from '@/app/init-post';

/** Services */
import preInitializer from '@/app/init-pre';
import {useHeyUni} from '@/heyuni-instance';

/** Application Bootstrapper */
const { Application } = useHeyUni();


// Add pre-initializers to application
Object.keys(preInitializer).forEach((key) => {
  // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
  const initializer = preInitializer[key]
  // @ts-expect-error
  // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
  Application.addInitializer(key, initializer, '-pre')
})

// Add initializers to application
Object.keys(initializers).forEach((key) => {
  // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
  const initializer = initializers[key]
  // @ts-expect-error
  // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
  Application.addInitializer(key, initializer)
})

// Add post-initializers to application
Object.keys(postInitializer).forEach((key) => {
  // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
  const initializer = postInitializer[key]

  // @ts-expect-error
  // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
  Application.addInitializer(key, initializer, '-post')
})
