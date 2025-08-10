.class Lcom/mikasa/codm/z;
.super Ljava/lang/Object;

# interfaces
.implements Ljava/lang/Runnable;


# instance fields
.field private final a:Lcom/mikasa/codm/x;

.field private final b:Landroid/app/ProgressDialog;

.field private final c:Landroid/os/Handler;


# direct methods
.method static constructor <clinit>()V
    .locals 0

    return-void
.end method

.method native constructor <init>(Lcom/mikasa/codm/x;Landroid/app/ProgressDialog;Landroid/os/Handler;)V
.end method

.method public static native ۟۟ۧۢۦ(Ljava/lang/Object;)Landroid/app/ProgressDialog;
.end method

.method public static native ۣۨۦۢ(Ljava/lang/Object;)Landroid/os/Handler;
.end method


# virtual methods
.method public native run()V
    .annotation runtime Ljava/lang/Override;
    .end annotation
.end method
